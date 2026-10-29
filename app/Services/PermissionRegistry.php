<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionRegistry
{
    /**
     * Get structured list of sidebar navigation menu definitions.
     */
    public static function getMenuDefinitions(): array
    {
        return [
            'Main' => [
                [
                    'key' => 'menu_dashboard',
                    'name' => 'Dashboard',
                    'path' => '/dashboard',
                    'icon' => 'GridIcon',
                    'description' => 'Access system dashboard overview and KPIs',
                ],
                [
                    'key' => 'menu_tasks',
                    'name' => 'Tasks & Schedule',
                    'path' => '/tasks',
                    'icon' => 'TaskIcon',
                    'description' => 'Manage task boards, timers, deadlines, and schedule reminders',
                ],
                [
                    'key' => 'menu_new_sourcing_request',
                    'name' => 'New Sourcing Request',
                    'path' => '/rfq',
                    'icon' => 'BoxCubeIcon',
                    'description' => 'Customer link to submit new sourcing RFQs',
                ],
                [
                    'key' => 'menu_new_visa_application',
                    'name' => 'New Visa Application',
                    'path' => '/visa-application',
                    'icon' => 'BoxCubeIcon',
                    'description' => 'Customer link to apply for visa processing',
                ],
                [
                    'key' => 'menu_my_payments',
                    'name' => 'My Payments',
                    'path' => '/payments',
                    'icon' => 'DocsIcon',
                    'description' => 'Customer payments history and receipt download',
                ],
            ],
            'Management' => [
                [
                    'key' => 'menu_users',
                    'name' => 'Users',
                    'path' => '/admin/users',
                    'icon' => 'UserGroupIcon',
                    'description' => 'Manage user accounts, categories, and role assignments',
                ],
                [
                    'key' => 'menu_rfqs',
                    'name' => 'RFQs',
                    'path' => '/admin/rfqs',
                    'icon' => 'ListIcon',
                    'description' => 'Manage sourcing RFQ submissions, statuses, and agent assignments',
                ],
                [
                    'key' => 'menu_sourcing_companies',
                    'name' => 'Sourcing Companies',
                    'path' => '/admin/sourcing-companies',
                    'icon' => 'BoxCubeIcon',
                    'description' => 'Manage trusted partner factories and sourcing suppliers',
                ],
                [
                    'key' => 'menu_transactions',
                    'name' => 'Transactions',
                    'path' => '/admin/payments',
                    'icon' => 'DocsIcon',
                    'description' => 'Audit system-wide customer transaction logs and Zainpay records',
                ],
                [
                    'key' => 'menu_categories',
                    'name' => 'Categories',
                    'path' => '/admin/categories',
                    'icon' => 'ListIcon',
                    'description' => 'Manage product categories and industry classifications',
                ],
                [
                    'key' => 'menu_contact_messages',
                    'name' => 'Contact Messages',
                    'path' => '/admin/contact-messages',
                    'icon' => 'ListIcon',
                    'description' => 'View, reply to, and archive incoming contact inquiries',
                ],
                [
                    'key' => 'menu_send_mail',
                    'name' => 'Send Mail',
                    'path' => '/admin/mail',
                    'icon' => 'BoxCubeIcon',
                    'description' => 'Compose and dispatch broadcast or targeted emails to users',
                ],
                [
                    'key' => 'menu_agent_activity',
                    'name' => 'Agent Activity',
                    'path' => '/admin/agent-activity',
                    'icon' => 'UserGroupIcon',
                    'description' => 'Monitor agent logins, sessions, and automated alert preferences',
                ],
                [
                    'key' => 'menu_roles_permissions',
                    'name' => 'Roles & Permissions',
                    'path' => '/admin/roles',
                    'icon' => 'PlugInIcon',
                    'description' => 'Configure roles, sidebar menu visibility, and granular action permissions',
                ],
            ],
            'Finance' => [
                [
                    'key' => 'menu_invoices',
                    'name' => 'Invoices',
                    'path' => '/finance/invoices',
                    'icon' => 'DocsIcon',
                    'description' => 'Generate, void, send, and record payments for invoices',
                ],
                [
                    'key' => 'menu_estimates',
                    'name' => 'Estimates',
                    'path' => '/finance/estimates',
                    'icon' => 'PageIcon',
                    'description' => 'Prepare and send price quotations and estimates to clients',
                ],
                [
                    'key' => 'menu_items',
                    'name' => 'Items',
                    'path' => '/finance/items',
                    'icon' => 'TableIcon',
                    'description' => 'Manage inventory item catalog, pricing, and Zoho sync',
                ],
                [
                    'key' => 'menu_expenses',
                    'name' => 'Expenses',
                    'path' => '/finance/expenses',
                    'icon' => 'PieChartIcon',
                    'description' => 'Track business expenses: Purchase, Shipment, Running Cost, and Salaries',
                ],
                [
                    'key' => 'menu_reports',
                    'name' => 'Reports',
                    'path' => '/finance/reports',
                    'icon' => 'BarChartIcon',
                    'description' => 'Review revenue, payments received, and outstanding receivables',
                ],
            ],
            'Visa Services' => [
                [
                    'key' => 'menu_visa_services',
                    'name' => 'Visa Services',
                    'path' => '/dashboard',
                    'icon' => 'ListIcon',
                    'description' => 'Visa application management and consular processing',
                ],
            ],
            'Account' => [
                [
                    'key' => 'menu_profile',
                    'name' => 'Profile',
                    'path' => '/profile',
                    'icon' => 'UserCircleIcon',
                    'description' => 'User profile information and security settings',
                ],
            ],
        ];
    }

    /**
     * Get structured list of action and capability permissions by module.
     */
    public static function getActionDefinitions(): array
    {
        return [
            'RFQs & Sourcing' => [
                [
                    'name' => 'view_rfqs',
                    'label' => 'View RFQs',
                    'description' => 'View customer RFQ submissions, attachments, and status details',
                ],
                [
                    'name' => 'create_rfqs',
                    'label' => 'Create RFQs',
                    'description' => 'Submit new sourcing RFQ requests',
                ],
                [
                    'name' => 'edit_rfqs',
                    'label' => 'Edit RFQs',
                    'description' => 'Update RFQ status, timeline, notes, and specifications',
                ],
                [
                    'name' => 'assign_rfqs',
                    'label' => 'Assign RFQs',
                    'description' => 'Assign incoming RFQs to procurement agents',
                ],
                [
                    'name' => 'delete_rfqs',
                    'label' => 'Delete RFQs',
                    'description' => 'Permanently remove RFQ records from the system',
                ],
                [
                    'name' => 'verify_rfq_payment',
                    'label' => 'Verify RFQ Payments',
                    'description' => 'Manually verify or sync payment status via Zainpay',
                ],
            ],
            'Users & Access Management' => [
                [
                    'name' => 'view_users',
                    'label' => 'View Users',
                    'description' => 'Browse registered users, agents, and client accounts',
                ],
                [
                    'name' => 'create_users',
                    'label' => 'Create Users',
                    'description' => 'Add new user accounts and agents to the system',
                ],
                [
                    'name' => 'edit_users',
                    'label' => 'Edit Users',
                    'description' => 'Update user details, category assignments, and roles',
                ],
                [
                    'name' => 'delete_users',
                    'label' => 'Delete Users',
                    'description' => 'Remove user accounts from the system',
                ],
                [
                    'name' => 'manage_users',
                    'label' => 'Manage Users (Full Control)',
                    'description' => 'Comprehensive user administration permissions',
                ],
                [
                    'name' => 'manage_roles',
                    'label' => 'Manage Roles',
                    'description' => 'Create, edit, and delete system roles and their permissions',
                ],
                [
                    'name' => 'manage_permissions',
                    'label' => 'Manage Permissions',
                    'description' => 'Define and update system access permission rules',
                ],
            ],
            'Finance: Invoices & Estimates' => [
                [
                    'name' => 'view_invoices',
                    'label' => 'View Invoices',
                    'description' => 'Browse invoices, payment statuses, and invoice details',
                ],
                [
                    'name' => 'create_invoices',
                    'label' => 'Create Invoices',
                    'description' => 'Issue new invoices or convert estimates to invoices',
                ],
                [
                    'name' => 'edit_invoices',
                    'label' => 'Edit / Void Invoices',
                    'description' => 'Modify invoice line items or void outstanding invoices',
                ],
                [
                    'name' => 'delete_invoices',
                    'label' => 'Delete Invoices',
                    'description' => 'Permanently delete draft or unissued invoices',
                ],
                [
                    'name' => 'send_invoices',
                    'label' => 'Send Invoices',
                    'description' => 'Dispatch invoice PDFs to client email addresses',
                ],
                [
                    'name' => 'record_invoice_payments',
                    'label' => 'Record Payments',
                    'description' => 'Log manual offline and bank transfer invoice payments',
                ],
                [
                    'name' => 'view_estimates',
                    'label' => 'View Estimates',
                    'description' => 'Review price quotes and cost estimates for clients',
                ],
                [
                    'name' => 'create_estimates',
                    'label' => 'Create Estimates',
                    'description' => 'Draft new estimates for RFQs and product sourcing',
                ],
                [
                    'name' => 'edit_estimates',
                    'label' => 'Edit Estimates',
                    'description' => 'Update estimate pricing, line items, and terms',
                ],
                [
                    'name' => 'delete_estimates',
                    'label' => 'Delete Estimates',
                    'description' => 'Remove cancelled or outdated estimates',
                ],
                [
                    'name' => 'send_estimates',
                    'label' => 'Send Estimates',
                    'description' => 'Send estimate quotes directly to customers',
                ],
                [
                    'name' => 'view_items',
                    'label' => 'View Catalog Items',
                    'description' => 'Browse product pricing and service items',
                ],
                [
                    'name' => 'manage_items',
                    'label' => 'Manage Catalog Items',
                    'description' => 'Create, update, sync with Zoho, or delete catalog items',
                ],
            ],
            'Finance: Expenses & Reports' => [
                [
                    'name' => 'view_expenses',
                    'label' => 'View Expenses',
                    'description' => 'Access company expense logs, filters, and receipts',
                ],
                [
                    'name' => 'create_expenses',
                    'label' => 'Create Expenses',
                    'description' => 'Record Purchase, Shipment, Running Cost, and Salary disbursements',
                ],
                [
                    'name' => 'edit_expenses',
                    'label' => 'Edit Expenses',
                    'description' => 'Modify recorded expenses and attach payment receipts',
                ],
                [
                    'name' => 'delete_expenses',
                    'label' => 'Delete Expenses',
                    'description' => 'Remove expense records from the database',
                ],
                [
                    'name' => 'export_expenses',
                    'label' => 'Export Expenses',
                    'description' => 'Download filtered expense records in CSV format',
                ],
                [
                    'name' => 'view_reports',
                    'label' => 'View Financial Reports',
                    'description' => 'Access invoice summaries, received payments, and receivables reports',
                ],
            ],
            'Tasks & Scheduled Talks' => [
                [
                    'name' => 'view_tasks',
                    'label' => 'View Tasks',
                    'description' => 'Access task boards, talk schedules, and due reminders',
                ],
                [
                    'name' => 'create_tasks',
                    'label' => 'Create Tasks',
                    'description' => 'Create new tasks and assign to team members',
                ],
                [
                    'name' => 'edit_tasks',
                    'label' => 'Edit Tasks',
                    'description' => 'Update task status, active timer, priority, and reminder dates',
                ],
                [
                    'name' => 'delete_tasks',
                    'label' => 'Delete Tasks',
                    'description' => 'Remove tasks from the task board',
                ],
            ],
            'Operations & Communications' => [
                [
                    'name' => 'manage_sourcing_companies',
                    'label' => 'Manage Sourcing Companies',
                    'description' => 'Create, edit, and remove supplier and factory partners',
                ],
                [
                    'name' => 'manage_categories',
                    'label' => 'Manage Categories',
                    'description' => 'Manage sourcing categories and industry classifications',
                ],
                [
                    'name' => 'manage_contact_messages',
                    'label' => 'Manage Contact Messages',
                    'description' => 'View, mark as read, reply, and delete customer messages',
                ],
                [
                    'name' => 'send_mail',
                    'label' => 'Send Broadcast Mail',
                    'description' => 'Send official emails and notifications to customers and staff',
                ],
                [
                    'name' => 'view_agent_activity',
                    'label' => 'View Agent Activity',
                    'description' => 'Audit agent login history, notification preferences, and active alerts',
                ],
                [
                    'name' => 'view_transactions',
                    'label' => 'View Transactions',
                    'description' => 'Monitor customer payments and Zainpay payment gateway records',
                ],
                [
                    'name' => 'view_dashboard',
                    'label' => 'View Dashboard Analytics',
                    'description' => 'View high-level overview metrics on the dashboard',
                ],
                [
                    'name' => 'manage_visa_applications',
                    'label' => 'Manage Visa Applications',
                    'description' => 'Review and update visa applicant statuses',
                ],
            ],
        ];
    }

    /**
     * Get all unique permission names (menus + actions).
     */
    public static function getAllPermissionNames(): array
    {
        $names = [];

        foreach (self::getMenuDefinitions() as $group => $items) {
            foreach ($items as $item) {
                $names[] = $item['key'];
            }
        }

        foreach (self::getActionDefinitions() as $module => $actions) {
            foreach ($actions as $action) {
                $names[] = $action['name'];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Pre-defined permission presets for easy 1-click role configuration.
     */
    public static function getPresets(): array
    {
        return [
            'admin' => [
                'name' => 'Full System Administrator',
                'description' => 'Grants all menus and all capabilities across the system.',
                'permissions' => self::getAllPermissionNames(),
            ],
            'agent' => [
                'name' => 'Procurement Agent',
                'description' => 'Access to Dashboard, Tasks, RFQs, Invoices, Estimates, and Items.',
                'permissions' => [
                    'menu_dashboard',
                    'menu_tasks',
                    'menu_new_sourcing_request',
                    'menu_invoices',
                    'menu_estimates',
                    'menu_items',
                    'menu_profile',
                    'view_dashboard',
                    'view_tasks',
                    'create_tasks',
                    'edit_tasks',
                    'view_rfqs',
                    'edit_rfqs',
                    'view_invoices',
                    'send_invoices',
                    'view_estimates',
                    'create_estimates',
                    'edit_estimates',
                    'send_estimates',
                    'view_items',
                ],
            ],
            'accountant' => [
                'name' => 'Accountant / Financial Officer',
                'description' => 'Comprehensive access to Dashboard, Transactions, Invoices, Estimates, Items, Expenses, Running Costs, and Financial Reports.',
                'permissions' => [
                    'menu_dashboard',
                    'menu_transactions',
                    'menu_invoices',
                    'menu_estimates',
                    'menu_items',
                    'menu_expenses',
                    'menu_reports',
                    'menu_profile',
                    'view_dashboard',
                    'view_transactions',
                    'view_invoices',
                    'create_invoices',
                    'edit_invoices',
                    'send_invoices',
                    'record_invoice_payments',
                    'view_estimates',
                    'create_estimates',
                    'edit_estimates',
                    'send_estimates',
                    'view_items',
                    'manage_items',
                    'view_expenses',
                    'create_expenses',
                    'edit_expenses',
                    'export_expenses',
                    'view_reports',
                ],
            ],
            'finance' => [
                'name' => 'Finance & Accounting Officer',
                'description' => 'Access to all financial modules, invoices, expenses, running costs, and reports.',
                'permissions' => [
                    'menu_dashboard',
                    'menu_transactions',
                    'menu_invoices',
                    'menu_estimates',
                    'menu_items',
                    'menu_expenses',
                    'menu_reports',
                    'menu_profile',
                    'view_dashboard',
                    'view_transactions',
                    'view_invoices',
                    'create_invoices',
                    'edit_invoices',
                    'send_invoices',
                    'record_invoice_payments',
                    'view_estimates',
                    'create_estimates',
                    'edit_estimates',
                    'send_estimates',
                    'view_items',
                    'manage_items',
                    'view_expenses',
                    'create_expenses',
                    'edit_expenses',
                    'export_expenses',
                    'view_reports',
                ],
            ],
            'support' => [
                'name' => 'Customer Support Officer',
                'description' => 'Access to RFQs, Contact Messages, Mail broadcast, and Client communications.',
                'permissions' => [
                    'menu_dashboard',
                    'menu_rfqs',
                    'menu_contact_messages',
                    'menu_send_mail',
                    'menu_profile',
                    'view_dashboard',
                    'view_rfqs',
                    'manage_contact_messages',
                    'send_mail',
                ],
            ],
            'visa' => [
                'name' => 'Visa Processing Agent',
                'description' => 'Access to Visa dashboard and application status workflows.',
                'permissions' => [
                    'menu_dashboard',
                    'menu_visa_services',
                    'menu_profile',
                    'view_dashboard',
                    'manage_visa_applications',
                ],
            ],
        ];
    }

    /**
     * Synchronize and ensure all permissions exist in database and assign defaults.
     */
    public static function syncAll(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissionNames = self::getAllPermissionNames();

        foreach ($allPermissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Grant all to admin, owner, super_admin
        $superRoles = ['admin', 'owner', 'super_admin'];
        foreach ($superRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->givePermissionTo(Permission::all());
        }

        // Grant defaults to agent and super_agent if existing
        $agentRole = Role::where('name', 'agent')->first();
        if ($agentRole) {
            $agentRole->givePermissionTo(self::getPresets()['agent']['permissions']);
        }

        $superAgentRole = Role::where('name', 'super_agent')->first();
        if ($superAgentRole) {
            $superAgentPermissions = array_unique(array_merge(
                self::getPresets()['agent']['permissions'],
                ['assign_rfqs', 'verify_rfq_payment', 'create_invoices']
            ));
            $superAgentRole->givePermissionTo($superAgentPermissions);
        }

        // Customer role
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $customerRole->givePermissionTo([
                'menu_dashboard',
                'menu_new_sourcing_request',
                'menu_new_visa_application',
                'menu_my_payments',
                'menu_profile',
                'view_dashboard',
                'create_rfqs',
            ]);
        }

        // Visa roles
        $visaAdmin = Role::where('name', 'visa_admin')->first();
        if ($visaAdmin) {
            $visaAdmin->givePermissionTo(self::getPresets()['visa']['permissions']);
        }

        $visaAgent = Role::where('name', 'visa_agent')->first();
        if ($visaAgent) {
            $visaAgent->givePermissionTo(self::getPresets()['visa']['permissions']);
        }

        // Accountant and Finance roles
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountantRole->syncPermissions(self::getPresets()['accountant']['permissions']);

        $financeRole = Role::where('name', 'finance')->first();
        if ($financeRole) {
            $financeRole->syncPermissions(self::getPresets()['finance']['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
