<?php

return [
    'GET' => [
        '/' => ['DashboardController', 'index'],
        '/dashboard' => ['DashboardController', 'index'],

        '/login' => ['AuthController', 'login'],
        '/logout' => ['AuthController', 'logout'],

        '/documents' => ['DocumentController', 'index'],
        '/documents/create' => ['DocumentController', 'create'],
        '/documents/edit' => ['DocumentController', 'edit'],
        '/documents/download' => ['DocumentController', 'download'],
        '/documents/type-file' => ['DocumentController', 'typeFile'],

        '/requests' => ['RequestController', 'index'],
        '/requests/export' => ['RequestController', 'export'],
        '/requests/create' => ['RequestController', 'create'],
        /* <!-- '/requests/show' => ['RequestController', 'show'], --> */
        '/requests/advance' => ['RequestController', 'advance'],
        '/requests/kanban' => ['RequestController', 'kanban'],
        '/requests/edit' => ['RequestController', 'edit'],
        '/requests/history' => ['RequestController', 'history'],
        '/planning' => ['PlanningController', 'index'],
        '/planning/show' => ['PlanningController', 'show'],
        '/planning/create' => ['PlanningController', 'create'],
        '/planning/gantt' => ['PlanningController', 'gantt'],
        '/planning/export' => ['PlanningController', 'export'],
        '/users' => ['UserController', 'index'],
        '/users/export' => ['UserController', 'export'],
        '/users/create' => ['UserController', 'create'],
        '/users/edit' => ['UserController', 'edit'],
        '/auth/google' => ['AuthController', 'googleRedirect'],
        '/auth/google/callback' => ['AuthController', 'googleCallback'],

        '/notifications' => ['NotificationController', 'index'],
        '/resources' => ['ProjectResourceController', 'index'],
        '/resources/create' => ['ProjectResourceController', 'create'],
        '/resources/edit' => ['ProjectResourceController', 'edit'],
        '/resources/file' => ['ProjectResourceController', 'file'],

        '/audit' => ['AuditController', 'index'],
    ],

    'POST' => [
        '/login' => ['AuthController', 'authenticate'],

        '/requests/store' => ['RequestController', 'store'],
        '/requests/update' => ['RequestController', 'update'],
        '/requests/change-status' => ['RequestController', 'changeStatus'],
        '/requests/block' => ['RequestController', 'block'],
        '/requests/unblock' => ['RequestController', 'unblock'],
        '/requests/comment' => ['RequestController', 'comment'],

        '/documents/store' => ['DocumentController', 'store'],
        '/documents/update' => ['DocumentController', 'update'],
        '/documents/toggle' => ['DocumentController', 'toggle'],
        '/documents/inactivate' => ['DocumentController', 'inactivate'],
        '/documents/reactivate' => ['DocumentController', 'reactivate'],
        '/documents/upload-phase' => ['DocumentController', 'uploadPhaseDocument'],
        '/documents/review' => ['DocumentController', 'review'],
        '/requests/change-phase' => ['DocumentController', 'changePhase'],
        '/planning/store' => ['PlanningController', 'store'],
        '/planning/milestone/store' => ['PlanningController', 'storeMilestone'],
        '/planning/task/store' => ['PlanningController', 'storeTask'],
        '/planning/task/update' => ['PlanningController', 'updateTask'],
        '/users/store' => ['UserController', 'store'],
        '/users/update' => ['UserController', 'update'],
        '/users/toggle' => ['UserController', 'toggle'],
        '/users/unlink-google' => ['UserController', 'unlinkGoogle'],

        '/notifications/mark-read' => ['NotificationController', 'markRead'],
        '/notifications/mark-all-read' => ['NotificationController', 'markAllRead'],
        '/resources/store' => ['ProjectResourceController', 'store'],
        '/resources/update' => ['ProjectResourceController', 'update'],
        '/resources/inactivate' => ['ProjectResourceController', 'inactivate'],
        '/resources/delete' => ['ProjectResourceController', 'delete'],

    ],
];