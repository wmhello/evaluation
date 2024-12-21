import Vue from "vue";
import Router from "vue-router";

Vue.use(Router);

/* Layout */
import Layout from "@/layout";

/**
 * Note: sub-menu only appear when route children.length >= 1
 * Detail see: https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 *
 * hidden: true                   if set true, item will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu
 *                                if not set alwaysShow, when item has more than one children route,
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noRedirect           if set noRedirect will no redirect in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    roles: ['admin','editor']    control the page roles (you can set multiple roles)
    title: 'title'               the name show in sidebar and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    breadcrumb: false            if set false, the item will hidden in breadcrumb(default is true)
    activeMenu: '/example/list'  if set path, the sidebar will highlight the path you set
  }
 */

/**
 * constantRoutes
 * a base page that does not have permission requirements
 * all roles can be accessed
 */
export const constantRoutes = [
  {
    path: "/login",
    component: () => import("@/views/login/index"),
    hidden: true
  },
  {
    path: "/oauth/github/callback",
    component: () => import("@/views/oauth/github"),
    hidden: true
  },
  {
    path: "/oauth/qq/callback",
    component: () => import("@/views/oauth/qq"),
    hidden: true
  },
  {
    path: "/oauth/gitee/callback",
    component: () => import("@/views/oauth/gitee"),
    hidden: true
  },
  {
    path: "/404",
    component: () => import("@/views/404"),
    hidden: true
  },
  {
    path: "/",
    component: Layout,
    redirect: "/dashboard",
    children: [
      {
        path: "dashboard",
        name: "Dashboard",
        component: () => import("@/views/dashboard/index"),
        meta: { title: "面板", icon: "dashboard", affix: true }
      }
    ]
  },
//   {
//     path: "/personal",
//     component: Layout,
//     redirect: "/personal/index",
//     hidden: true,
//     children: [
//       {
//         path: "index",
//         name: "personal_index",
//         component: () => import("@/views/info/index"),
//         hidden: true,
//         meta: {
//           title: "个人信息修改",
//           icon: "dashboard"
//         }
//       }
//     ]
//   }
];

/**
 * asyncRoutes
 * the routes that need to be dynamically loaded based on user roles
 */
export const asyncRoutes = [
  // {
  //   path: "/admin",
  //   component: Layout,
  //   meta: {
  //     title: "系统管理",
  //     icon: "category",
  //     roles: ["admin.menu", "roles.menu", "modules.menu", "table.menu"]
  //   },
  //   redirect: "/admin/index",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "index",
  //       name: "admin_index",
  //       component: () => import("@/views/system/admin/index"),
  //       meta: {
  //         title: "用户管理",
  //         roles: ["admin.menu"],
  //         icon: "dashboard"
  //       }
  //     },
  //     {
  //       path: "role",
  //       name: "role_index",
  //       component: () => import("@/views/system/role/index"),
  //       meta: {
  //         title: "角色管理",
  //         roles: ["roles.menu"],
  //         icon: "form"
  //       }
  //     },
  //     {
  //       path: "permission",
  //       name: "permission_index",
  //       component: () => import("@/views/system/module/index"),
  //       meta: {
  //         title: "模块与功能管理",
  //         roles: ["modules.menu"],
  //         icon: "lock"
  //       }
  //     },

  //   ]
  // },
  // {
  //   path: "/sys",
  //   component: Layout,
  //   meta: {
  //     title: "系统工具",
  //     icon: "category",
  //     roles: ["tables.menu", "code_snippets.menu"]
  //   },
  //   redirect: "/sys/table",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "table",
  //       name: "TableIndex",
  //       component: () => import("@/views/system/table/index"),
  //       meta: {
  //         title: "代码生成",
  //         roles: ["tables.menu"],
  //         icon: "lock"
  //       }
  //     },
  //     {
  //       path: "preview",
  //       name: "PreviewIndex",
  //       hidden: true,
  //       activeMenu: '/sys/table',
  //       component: () => import("@/views/system/table/preview"),
  //       meta: {
  //         title: "代码预览",
  //         roles: ["tables.menu"],
  //         icon: "lock"
  //       }
  //     },
  //     {
  //       path: "table_config",
  //       name: "TableConfigIndex",
  //       hidden: true,
  //       activeMenu: '/sys/table',
  //       component: () => import("@/views/system/table/config"),
  //       meta: {
  //         title: "详细配置",
  //         roles: ["table_configs.menu"],
  //         icon: "lock"
  //       }
  //     },
  //     {
  //       path: "snippet",
  //       name: "SnippetIndex",
  //       component: () => import("@/views/system/snippet/index"),
  //       meta: {
  //         title: "代码片段",
  //         roles: ["code_snippets.menu"],
  //         icon: "lock"
  //       }
  //     },
  //   ]
  // },
  // {
  //   path: "/contents",
  //   component: Layout,
  //   meta: {
  //     title: "内容管理",
  //     icon: "pdf",
  //     roles: ["carousels.menu", "articles.menu", "article_categories.menu"]
  //   },
  //   redirect: "/content/carousel",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "carousel",
  //       name: "carousel_index",
  //       component: () => import("@/views/content/carousel/index"),
  //       meta: {
  //         title: "轮播图管理",
  //         roles: ["carousels.menu"],
  //         icon: "list"
  //       }
  //     },
  //     {
  //       path: "article_category",
  //       name: "article_category_index",
  //       component: () => import("@/views/content/article_category/index"),
  //       meta: {
  //         title: "文章类型管理",
  //         roles: ["article_categories.menu"],
  //         icon: "form"
  //       }
  //     },
  //     {
  //       path: "article",
  //       name: "article_index",
  //       component: () => import("@/views/content/article/index"),
  //       meta: {
  //         title: "文章管理",
  //         roles: ["articles.menu"],
  //         icon: "article"
  //       }
  //     }
  //   ]
  // },
  // {
  //   path: "/message",
  //   component: Layout,
  //   meta: {
  //     title: "系统通讯",
  //     icon: "pdf",
  //     roles: ["serveices.menu"]
  //   },
  //   redirect: "/message/service",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "service",
  //       name: "ServiceIndex",
  //       component: () => import("@/views/message/service/index"),
  //       meta: {
  //         title: "客服演示(用户)",
  //         roles: ["serveices.menu"],
  //         icon: "list"
  //       }
  //     },
  //     {
  //       path: "customer",
  //       name: "CustomerIndex",
  //       component: () => import("@/views/message/service/customer"),
  //       meta: {
  //         title: "客服演示(客服)",
  //         roles: ["serveices.menu"],
  //         icon: "list"
  //       }
  //     },
  //     {
  //       path: "chat",
  //       name: "ChatIndex",
  //       component: () => import("@/views/message/chat/index"),
  //       meta: {
  //         title: "聊天室",
  //         roles: ["chats.menu"],
  //         icon: "list"
  //       }
  //     },
  //   ]
  // },
  // {
  //   path: "/common",
  //   component: Layout,
  //   meta: {
  //     title: "通用管理",
  //     icon: "pdf",
  //     roles: ["wechats.menu"]
  //   },
  //   redirect: "/common/wechat",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "wechat",
  //       name: "WechatIndex",
  //       component: () => import("@/views/wechat/index"),
  //       meta: {
  //         title: "微信设置",
  //         roles: ["wechats.menu"],
  //         icon: "list"
  //       }
  //     }
  //   ]
  // },
  // {
  //   path: "/base",
  //   component: Layout,
  //   meta: {
  //     title: "基础设置",
  //     icon: "pdf",
  //     roles: ["wechats.menu"]
  //   },
  //   redirect: "/base/content",
  //   alwaysShow: true,
  //   children: [
  //     {
  //       path: "content",
  //       name: "GradeIndex",
  //       component: () => import("@/views/grade/index"),
  //       meta: {
  //         title: "内容管理",
  //         roles: ["wechats.menu"],
  //         icon: "list"
  //       }
  //     },
  //     {
  //       path: "person",
  //       name: "GradeIndex",
  //       component: () => import("@/views/grade/index"),
  //       meta: {
  //         title: "人员管理",
  //         roles: ["wechats.menu"],
  //         icon: "list"
  //       }
  //     },
  //     {
  //       path: "group",
  //       name: "GradeIndex",
  //       component: () => import("@/views/grade/index"),
  //       meta: {
  //         title: "组管理",
  //         roles: ["wechats.menu"],
  //         icon: "list"
  //       }
  //     },

  //   ]
  // },
  {
    path: "/eval",
    component: Layout,
    meta: {
      title: "测评管理",
      icon: "pdf",
      roles: ["wechats.menu"]
    },
    redirect: "/eval/grade",
    alwaysShow: true,
    children: [
      {
        path: "grade",
        name: "GradeIndex",
        component: () => import("@/views/grade/index"),
        meta: {
          title: "等级填写",
          roles: ["wechats.menu"],
          icon: "list"
        }
      },
      {
        path: "grade_count",
        name: "GradeCountIndex",
        component: () => import("@/views/grade_count/index"),
        meta: {
          title: "等级统计",
          roles: ["wechats.menu"],
          icon: "list"
        }
      },
      {
        path: "grade_analysis",
        name: "GradeAnalysisIndex",
        component: () => import("@/views/grade_analysis/index"),
        meta: {
          title: "等级分析",
          roles: ["wechats.menu"],
          icon: "list"
        }
      }
    ]
  },
  // 404 page must be placed at the end !!!
  { path: "*", redirect: "/404", hidden: true }
];

const createRouter = () =>
  new Router({
    // base: "/",
    mode: "hash", // require service support
    scrollBehavior: () => ({ y: 0 }),
    routes: constantRoutes
  });

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  router.matcher = newRouter.matcher; // reset router
}

export default router;
