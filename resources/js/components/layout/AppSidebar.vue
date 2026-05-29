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
  BarChartIcon
} from "../../icons";
import { useSidebar } from "@/composables/useSidebar";

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();
const page = usePage();

const hasRole = (roles) => {
  const userRoles = page.props.auth.user?.roles || [];
  return roles.some((role) => userRoles.includes(role));
};

const menuGroups = computed(() => {
  const groups = [
    {
      title: "Main",
      items: [
        {
          icon: GridIcon,
          name: "Dashboard",
          path: "/dashboard",
        },
        {
          icon: BoxCubeIcon,
          name: "New Request",
          path: "/rfq",
          external: true,
        },
      ],
    },
  ];

  if (hasRole(["admin", "owner"])) {
    groups.push({
      title: "Management",
      items: [
        {
          icon: UserGroupIcon,
          name: "Users",
          path: "/admin/users",
        },
        {
          icon: ListIcon,
          name: "RFQs",
          path: "/admin/rfqs",
        },
        {
          icon: BoxCubeIcon,
          name: "Sourcing Companies",
          path: "/admin/sourcing-companies",
        },
        {
          icon: ListIcon,
          name: "Categories",
          path: "/admin/categories",
        },
        {
          icon: PlugInIcon,
          name: "Roles & Permissions",
          subItems: [
            { name: "Roles", path: "/admin/roles" },
            { name: "Permissions", path: "/admin/permissions" },
          ],
        },
      ],
    });
  }

  if (hasRole(["admin", "owner", "agent", "super_agent"])) {
    const financeItems = [
      {
        icon: DocsIcon,
        name: "Invoices",
        path: "/finance/invoices",
      },
      {
        icon: PageIcon,
        name: "Estimates",
        path: "/finance/estimates",
      },
      {
        icon: TableIcon,
        name: "Items",
        path: "/finance/items",
      },
    ];

    if (hasRole(["admin", "owner"])) {
      financeItems.push({
        icon: BarChartIcon,
        name: "Reports",
        path: "/finance/reports",
      });
    }

    groups.push({
      title: "Finance",
      items: financeItems,
    });
  }

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
