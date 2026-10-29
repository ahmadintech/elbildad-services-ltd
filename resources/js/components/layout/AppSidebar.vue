<template>
  <aside
    :class="[
      'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200',
      {
        'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
        'lg:w-[90px]': !isExpanded && !isHovered,
        'translate-x-0 w-[290px]': isMobileOpen,
        '-translate-x-full': !isMobileOpen,
        'lg:translate-x-0': true,
      },
    ]"
    @mouseenter="!isExpanded && (isHovered = true)"
    @mouseleave="isHovered = false"
  >
    <div
      :class="[
        'py-8 flex',
        !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
      ]"
    >
      <Link href="/dashboard" class="flex items-center gap-2">
        <template v-if="isExpanded || isHovered || isMobileOpen">
          <img
            class="h-10 w-auto"
            src="/images/logo/elbildad-logo.png"
            alt="Elbildad Services Ltd"
          />
          <div class="flex flex-col leading-none">
            <span class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Elbildad</span>
            <span class="text-[10px] font-medium text-brand-500 uppercase tracking-widest">Services</span>
          </div>
        </template>
        <img
          v-else
          class="h-8 w-auto"
          src="/images/logo/elbildad-logo.png"
          alt="Elbildad Services Ltd"
        />
      </Link>
    </div>
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
            <h2
              :class="[
                'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
                !isExpanded && !isHovered
                  ? 'lg:justify-center'
                  : 'justify-start',
              ]"
            >
              <template v-if="isExpanded || isHovered || isMobileOpen">
                {{ menuGroup.title }}
              </template>
              <HorizontalDots v-else />
            </h2>
            <ul class="flex flex-col gap-4">
              <li v-for="(item, index) in menuGroup.items" :key="item.name">
                <button
                  v-if="item.subItems"
                  @click="toggleSubmenu(groupIndex, index)"
                  :class="[
                    'menu-item group w-full',
                    {
                      'menu-item-active': isSubmenuOpen(groupIndex, index),
                      'menu-item-inactive': !isSubmenuOpen(groupIndex, index),
                    },
                    !isExpanded && !isHovered
                      ? 'lg:justify-center'
                      : 'lg:justify-start',
                  ]"
                >
                  <span
                    :class="[
                      isSubmenuOpen(groupIndex, index)
                        ? 'menu-item-icon-active'
                        : 'menu-item-icon-inactive',
                    ]"
                  >
                    <component :is="item.icon" />
                  </span>
                  <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="menu-item-text ml-3"
                    >{{ item.name }}</span
                  >
                  <ChevronDownIcon
                    v-if="isExpanded || isHovered || isMobileOpen"
                    :class="[
                      'ml-auto w-5 h-5 transition-transform duration-200',
                      {
                        'rotate-180 text-brand-500': isSubmenuOpen(
                          groupIndex,
                          index
                        ),
                      },
                    ]"
                  />
                </button>
                <template v-if="item.path">
                  <a
                    v-if="item.external"
                    :href="item.path"
                    class="menu-item group menu-item-inactive"
                  >
                    <span class="menu-item-icon-inactive">
                      <component :is="item.icon" />
                    </span>
                    <span
                      v-if="isExpanded || isHovered || isMobileOpen"
                      class="menu-item-text ml-3"
                    >{{ item.name }}</span>
                  </a>
                  <Link
                    v-else
                    :href="item.path"
                    :class="[
                      'menu-item group',
                      {
                        'menu-item-active': $page.url === item.path,
                        'menu-item-inactive': $page.url !== item.path,
                      },
                    ]"
                  >
                    <span
                      :class="[
                        $page.url === item.path
                          ? 'menu-item-icon-active'
                          : 'menu-item-icon-inactive',
                      ]"
                    >
                      <component :is="item.icon" />
                    </span>
                    <span
                      v-if="isExpanded || isHovered || isMobileOpen"
                      class="menu-item-text ml-3"
                    >{{ item.name }}</span>
                  </Link>
                </template>
                <div
                    v-show="
                      isSubmenuOpen(groupIndex, index) &&
                      (isExpanded || isHovered || isMobileOpen)
                    "
                  >
                    <ul class="mt-2 space-y-1 ml-9">
                      <li v-for="subItem in item.subItems" :key="subItem.name">
                        <Link
                          :href="subItem.path"
                          :class="[
                            'menu-dropdown-item',
                            {
                              'menu-dropdown-item-active': $page.url === subItem.path,
                              'menu-dropdown-item-inactive': $page.url !== subItem.path,
                            },
                          ]"
                        >
                          {{ subItem.name }}
                        </Link>
                      </li>
                    </ul>
                  </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
  GridIcon,
  UserCircleIcon,
  ChevronDownIcon,
  HorizontalDots,
  TableIcon,
  ListIcon,
  UserGroupIcon,
  BoxCubeIcon,
  PlugInIcon,
  DocsIcon,
  PageIcon,
  BarChartIcon,
  PieChartIcon,
  TaskIcon
} from "../../icons";
import { useSidebar } from "@/composables/useSidebar";

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();
const page = usePage();

const hasRole = (roles) => {
  const userRoles = page.props.auth.user?.roles || [];
  return roles.some((role) => userRoles.includes(role));
};

const hasPermission = (permission) => {
  if (hasRole(["admin", "owner", "super_admin"])) return true;
  const userPermissions = page.props.auth?.permissions || page.props.auth.user?.permissions || [];
  return userPermissions.includes(permission);
};

const canAccess = (menuKey, actionPerms = [], fallbackRoles = []) => {
  if (hasRole(["admin", "owner", "super_admin"])) return true;
  const userPermissions = page.props.auth?.permissions || page.props.auth.user?.permissions || [];
  if (userPermissions.includes(menuKey)) return true;
  if (actionPerms.some((p) => userPermissions.includes(p))) return true;
  if (fallbackRoles.length > 0 && hasRole(fallbackRoles)) return true;
  return false;
};

const menuGroups = computed(() => {
  const groups = [];

  // 1. Main Navigation Group
  const mainItems = [];

  if (canAccess("menu_dashboard", ["view_dashboard"], ["admin", "owner", "super_admin", "agent", "super_agent", "customer", "visa_admin", "visa_agent", "accountant", "finance"])) {
    mainItems.push({
      icon: GridIcon,
      name: "Dashboard",
      path: "/dashboard",
    });
  }

  if (canAccess("menu_tasks", ["view_tasks", "create_tasks", "edit_tasks"], ["admin", "owner", "super_admin", "agent", "super_agent"])) {
    mainItems.push({
      icon: TaskIcon,
      name: "Tasks & Schedule",
      path: "/tasks",
    });
  }

  if (canAccess("menu_new_sourcing_request", ["create_rfqs"], ["admin", "owner", "super_admin", "customer", "agent", "super_agent"])) {
    mainItems.push({
      icon: BoxCubeIcon,
      name: "New Sourcing Request",
      path: "/rfq",
      external: true,
    });
  }

  if (canAccess("menu_new_visa_application", ["manage_visa_applications"], ["admin", "owner", "super_admin", "customer"])) {
    mainItems.push({
      icon: BoxCubeIcon,
      name: "New Visa Application",
      path: "/visa-application",
      external: true,
    });
  }

  if (canAccess("menu_my_payments", [], ["customer"])) {
    mainItems.push({
      icon: DocsIcon,
      name: "My Payments",
      path: "/payments",
    });
  }

  if (mainItems.length > 0) {
    groups.push({
      title: "Main",
      items: mainItems,
    });
  }

  // 2. Management Group
  const managementItems = [];

  if (canAccess("menu_users", ["view_users", "manage_users"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: UserGroupIcon,
      name: "Users",
      path: "/admin/users",
    });
  }

  if (canAccess("menu_rfqs", ["view_rfqs", "edit_rfqs", "assign_rfqs"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: ListIcon,
      name: "RFQs",
      path: "/admin/rfqs",
    });
  }

  if (canAccess("menu_sourcing_companies", ["manage_sourcing_companies"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: BoxCubeIcon,
      name: "Sourcing Companies",
      path: "/admin/sourcing-companies",
    });
  }

  if (canAccess("menu_transactions", ["view_transactions"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: DocsIcon,
      name: "Transactions",
      path: "/admin/payments",
    });
  }

  if (canAccess("menu_categories", ["manage_categories"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: ListIcon,
      name: "Categories",
      path: "/admin/categories",
    });
  }

  if (canAccess("menu_contact_messages", ["manage_contact_messages"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: ListIcon,
      name: "Contact Messages",
      path: "/admin/contact-messages",
    });
  }

  if (canAccess("menu_send_mail", ["send_mail"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: BoxCubeIcon,
      name: "Send Mail",
      path: "/admin/mail",
    });
  }

  if (canAccess("menu_agent_activity", ["view_agent_activity"], ["admin", "owner", "super_admin"])) {
    managementItems.push({
      icon: UserGroupIcon,
      name: "Agent Activity",
      path: "/admin/agent-activity",
    });
  }

  if (canAccess("menu_roles_permissions", ["manage_roles", "manage_permissions"], ["admin", "owner", "super_admin"])) {
    const roleSubItems = [];
    if (canAccess("menu_roles_permissions", ["manage_roles"], ["admin", "owner", "super_admin"])) {
      roleSubItems.push({ name: "Roles", path: "/admin/roles" });
    }
    if (canAccess("menu_roles_permissions", ["manage_permissions"], ["admin", "owner", "super_admin"])) {
      roleSubItems.push({ name: "Permissions", path: "/admin/permissions" });
    }

    managementItems.push({
      icon: PlugInIcon,
      name: "Roles & Permissions",
      subItems: roleSubItems.length > 0 ? roleSubItems : [{ name: "Roles", path: "/admin/roles" }],
    });
  }

  if (managementItems.length > 0) {
    groups.push({
      title: "Management",
      items: managementItems,
    });
  }

  // 3. Finance Group
  const financeItems = [];

  if (canAccess("menu_invoices", ["view_invoices", "create_invoices", "edit_invoices"], ["admin", "owner", "super_admin", "agent", "super_agent", "accountant", "finance"])) {
    financeItems.push({
      icon: DocsIcon,
      name: "Invoices",
      path: "/finance/invoices",
    });
  }

  if (canAccess("menu_estimates", ["view_estimates", "create_estimates", "edit_estimates"], ["admin", "owner", "super_admin", "agent", "super_agent", "accountant", "finance"])) {
    financeItems.push({
      icon: PageIcon,
      name: "Estimates",
      path: "/finance/estimates",
    });
  }

  if (canAccess("menu_items", ["view_items", "manage_items"], ["admin", "owner", "super_admin", "agent", "super_agent", "accountant", "finance"])) {
    financeItems.push({
      icon: TableIcon,
      name: "Items",
      path: "/finance/items",
    });
  }

  if (canAccess("menu_expenses", ["view_expenses", "create_expenses", "edit_expenses"], ["admin", "owner", "super_admin", "accountant", "finance"])) {
    financeItems.push({
      icon: PieChartIcon,
      name: "Expenses",
      path: "/finance/expenses",
    });
  }

  if (canAccess("menu_reports", ["view_reports"], ["admin", "owner", "super_admin", "accountant", "finance"])) {
    financeItems.push({
      icon: BarChartIcon,
      name: "Reports",
      path: "/finance/reports",
    });
  }

  if (financeItems.length > 0) {
    groups.push({
      title: "Finance",
      items: financeItems,
    });
  }

  // 4. Visa Services Group
  if (canAccess("menu_visa_services", ["manage_visa_applications"], ["visa_admin", "visa_agent"])) {
    groups.push({
      title: "Visa Services",
      items: [
        {
          icon: ListIcon,
          name: "Visa Dashboard",
          path: "/dashboard",
        },
      ],
    });
  }

  // 5. Account Group
  if (canAccess("menu_profile", [], ["admin", "owner", "super_admin", "agent", "super_agent", "customer", "visa_admin", "visa_agent", "accountant", "finance"])) {
    groups.push({
      title: "Account",
      items: [
        {
          icon: UserCircleIcon,
          name: "Profile",
          path: "/profile",
        },
      ],
    });
  }

  return groups;
});

const toggleSubmenu = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isSubmenuOpen = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  return openSubmenu.value === key;
};
</script>
