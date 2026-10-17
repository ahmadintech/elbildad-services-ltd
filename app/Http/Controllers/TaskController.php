<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display the monday.com-style Task / Todo board and schedule.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'owner']);

        $query = Task::accessibleBy($user)
            ->with(['assignedAgent:id,name,email', 'creator:id,name', 'rfq:id,product_name,tracking_token']);

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($isAdmin && $request->filled('agent_id') && $request->agent_id !== 'all') {
            $query->where('assigned_to', $request->agent_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('time_filter')) {
            $now = Carbon::now();
            if ($request->time_filter === 'today') {
                $query->whereDate('due_at', $now->toDateString());
            } elseif ($request->time_filter === 'upcoming') {
                $query->where('due_at', '>', $now);
            } elseif ($request->time_filter === 'overdue') {
                $query->where('due_at', '<', $now)->where('status', '!=', 'done');
            }
        }

        $tasks = $query->orderByRaw("
            CASE 
                WHEN status = 'stuck' THEN 1
                WHEN status = 'in_progress' THEN 2
                WHEN status = 'pending' THEN 3
                WHEN status = 'done' THEN 4
                ELSE 5
            END
        ")
        ->orderByRaw("CASE WHEN due_at IS NULL THEN 1 ELSE 0 END, due_at ASC")
        ->get()
        ->map(function ($task) {
            $now = Carbon::now();
            $isOverdue = $task->due_at && $task->due_at->isPast() && $task->status !== 'done';
            
            // Format human friendly due label
            $dueLabel = 'No due date';
            if ($task->due_at) {
                if ($task->due_at->isToday()) {
                    $dueLabel = 'Today ' . $task->due_at->format('h:i A');
                } elseif ($task->due_at->isTomorrow()) {
                    $dueLabel = 'Tomorrow ' . $task->due_at->format('h:i A');
                } elseif ($task->due_at->isYesterday()) {
                    $dueLabel = 'Yesterday ' . $task->due_at->format('h:i A');
                } else {
                    $dueLabel = $task->due_at->format('M d, Y h:i A');
                }
            }

            // If a live timer is active, compute live elapsed minutes
            $liveDuration = $task->duration_minutes ?? 0;
            if ($task->timing_started_at) {
                $liveDuration += (int) $task->timing_started_at->diffInMinutes($now);
            }

            return [
                'id'                => $task->id,
                'title'             => $task->title,
                'description'       => $task->description,
                'type'              => $task->type,
                'status'            => $task->status,
                'priority'          => $task->priority,
                'contact_name'      => $task->contact_name,
                'contact_phone'     => $task->contact_phone,
                'due_at'            => $task->due_at?->toISOString(),
                'due_label'         => $dueLabel,
                'is_overdue'        => $isOverdue,
                'reminder_at'       => $task->reminder_at?->toISOString(),
                'reminder_label'    => $task->reminder_at ? $task->reminder_at->format('M d, h:i A') : null,
                'duration_minutes'  => $liveDuration,
                'is_timing'         => !is_null($task->timing_started_at),
                'timing_started_at' => $task->timing_started_at?->toISOString(),
                'completed_at'      => $task->completed_at?->format('M d, Y H:i'),
                'notes'             => $task->notes,
                'assigned_agent'    => $task->assignedAgent ? [
                    'id'    => $task->assignedAgent->id,
                    'name'  => $task->assignedAgent->name,
                    'email' => $task->assignedAgent->email,
                ] : null,
                'creator'           => $task->creator ? [
                    'id'   => $task->creator->id,
                    'name' => $task->creator->name,
                ] : null,
                'rfq'               => $task->rfq ? [
                    'id'             => $task->rfq->id,
                    'product_name'   => $task->rfq->product_name,
                    'tracking_token' => $task->rfq->tracking_token,
                ] : null,
                'created_at'        => $task->created_at->format('M d, Y'),
            ];
        });

        // Compute high-level stats for user / admin
        $allAccessible = Task::accessibleBy($user);
        $totalCount    = (clone $allAccessible)->count();
        $doneCount     = (clone $allAccessible)->where('status', 'done')->count();
        $inProgressCount = (clone $allAccessible)->where('status', 'in_progress')->count();
        $pendingCount  = (clone $allAccessible)->where('status', 'pending')->count();
        $stuckCount    = (clone $allAccessible)->where('status', 'stuck')->count();
        $talkCount     = (clone $allAccessible)->where('type', 'talk')->count();
        $overdueCount  = (clone $allAccessible)->where('due_at', '<', now())->where('status', '!=', 'done')->count();

        $stats = [
            'total'       => $totalCount,
            'done'        => $doneCount,
            'in_progress' => $inProgressCount,
            'pending'     => $pendingCount,
            'stuck'       => $stuckCount,
            'talks'       => $talkCount,
            'overdue'     => $overdueCount,
            'completion_rate' => $totalCount > 0 ? round(($doneCount / $totalCount) * 100, 1) : 0,
        ];

        // Eligible agents list for assignments
        $agents = User::role(['agent', 'super_agent', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Recent RFQs for quick relation
        $rfqs = Rfq::latest()
            ->take(40)
            ->get(['id', 'product_name', 'tracking_token']);

        return Inertia::render('Tasks/Index', [
            'tasks'    => $tasks,
            'stats'    => $stats,
            'agents'   => $agents,
            'rfqs'     => $rfqs,
            'isAdmin'  => $isAdmin,
            'filters'  => $request->only(['status', 'priority', 'type', 'agent_id', 'search', 'time_filter']),
        ]);
    }

    /**
     * Store a new task or scheduled talk.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'type'             => ['required', 'string', 'in:talk,rfq_followup,meeting,supplier_contact,general'],
            'priority'         => ['required', 'string', 'in:low,medium,high,urgent'],
            'status'           => ['nullable', 'string', 'in:pending,in_progress,done,stuck'],
            'assigned_to'      => ['nullable', 'exists:users,id'],
            'rfq_id'           => ['nullable', 'exists:rfqs,id'],
            'contact_name'     => ['nullable', 'string', 'max:255'],
            'contact_phone'    => ['nullable', 'string', 'max:50'],
            'due_at'           => ['nullable', 'date'],
            'reminder_at'      => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        
        // If agent doesn't assign to someone else, assign to self
        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $user->id;
        }

        $validated['user_id'] = $user->id;
        $validated['status']  = $validated['status'] ?? 'pending';

        if ($validated['status'] === 'done') {
            $validated['completed_at'] = now();
            $validated['completed_by'] = $user->id;
        }

        Task::create($validated);

        return back()->with('success', 'Task / talk scheduled successfully.');
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request->user(), $task);

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'type'             => ['required', 'string', 'in:talk,rfq_followup,meeting,supplier_contact,general'],
            'priority'         => ['required', 'string', 'in:low,medium,high,urgent'],
            'status'           => ['required', 'string', 'in:pending,in_progress,done,stuck'],
            'assigned_to'      => ['nullable', 'exists:users,id'],
            'rfq_id'           => ['nullable', 'exists:rfqs,id'],
            'contact_name'     => ['nullable', 'string', 'max:255'],
            'contact_phone'    => ['nullable', 'string', 'max:50'],
            'due_at'           => ['nullable', 'date'],
            'reminder_at'      => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ]);

        // Manage completion tracking
        if ($validated['status'] === 'done' && $task->status !== 'done') {
            $validated['completed_at'] = now();
            $validated['completed_by'] = $request->user()->id;
        } elseif ($validated['status'] !== 'done' && $task->status === 'done') {
            $validated['completed_at'] = null;
            $validated['completed_by'] = null;
        }

        // If reminder was adjusted into the future, reset reminder_sent_at
        if (!empty($validated['reminder_at']) && Carbon::parse($validated['reminder_at'])->isFuture()) {
            $validated['reminder_sent_at'] = null;
        }

        $task->update($validated);

        return back()->with('success', 'Task updated successfully.');
    }

    /**
     * Quick status change (monday.com one-click toggle).
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request->user(), $task);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,done,stuck'],
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'done') {
            $updates['completed_at'] = now();
            $updates['completed_by'] = $request->user()->id;
            // Stop timer if it was running
            if ($task->timing_started_at) {
                $elapsed = (int) $task->timing_started_at->diffInMinutes(now());
                $updates['duration_minutes'] = ($task->duration_minutes ?? 0) + $elapsed;
                $updates['timing_started_at'] = null;
            }
        } else {
            $updates['completed_at'] = null;
            $updates['completed_by'] = null;
        }

        $task->update($updates);

        return back()->with('success', "Task marked as {$validated['status']}.");
    }

    /**
     * Start or stop live timer on a task.
     */
    public function toggleTimer(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request->user(), $task);

        if ($task->timing_started_at) {
            // Stop timer
            $elapsed = (int) $task->timing_started_at->diffInMinutes(now());
            $task->update([
                'duration_minutes'  => ($task->duration_minutes ?? 0) + $elapsed,
                'timing_started_at' => null,
            ]);
            $msg = 'Timer paused.';
        } else {
            // Start timer
            $task->update([
                'timing_started_at' => now(),
                'status'            => 'in_progress',
            ]);
            $msg = 'Timer started.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTaskAccess($request->user(), $task);

        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    /**
     * Live API endpoint: check for due reminders to trigger audio + browser alerts.
     */
    public function dueReminders(Request $request): JsonResponse
    {
        $user = $request->user();

        $dueTasks = Task::dueRemindersFor($user)
            ->with(['assignedAgent:id,name', 'rfq:id,product_name'])
            ->orderBy('reminder_at', 'asc')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id'            => $t->id,
                'title'         => $t->title,
                'type'          => $t->type,
                'priority'      => $t->priority,
                'contact_name'  => $t->contact_name,
                'contact_phone' => $t->contact_phone,
                'due_at'        => $t->due_at?->format('h:i A'),
                'reminder_at'   => $t->reminder_at?->toISOString(),
            ]);

        return response()->json([
            'reminders' => $dueTasks,
        ]);
    }

    /**
     * Acknowledge or snooze a reminder.
     */
    public function acknowledgeReminder(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTaskAccess($request->user(), $task);

        if ($request->boolean('snooze')) {
            // Snooze by 10 minutes
            $task->update([
                'reminder_at'      => now()->addMinutes(10),
                'reminder_sent_at' => null,
            ]);
            return response()->json(['status' => 'snoozed', 'next_alert' => 'in 10 minutes']);
        }

        $task->update([
            'reminder_sent_at' => now(),
        ]);

        return response()->json(['status' => 'acknowledged']);
    }

    /**
     * Ensure user is allowed to mutate the task.
     */
    private function authorizeTaskAccess($user, Task $task): void
    {
        if ($user->hasAnyRole(['admin', 'owner'])) {
            return;
        }

        if ($task->assigned_to !== $user->id && $task->user_id !== $user->id) {
            abort(403, 'Unauthorized action on this task.');
        }
    }
}
