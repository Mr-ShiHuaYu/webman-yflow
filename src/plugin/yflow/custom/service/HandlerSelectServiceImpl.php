<?php

namespace plugin\yflow\custom\service;

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\Role;
use Yflow\ui\dto\HandlerFunDto;
use Yflow\ui\dto\HandlerQuery;
use Yflow\ui\dto\TreeFunDto;
use Yflow\ui\service\HandlerSelectService;
use Yflow\ui\service\HandlerSelectServiceTrait;
use Yflow\ui\vo\HandlerFeedBackVo;
use Yflow\ui\vo\HandlerSelectVo;

/**
 * 用于流程设计器中选择办理人页面
 */
class HandlerSelectServiceImpl implements HandlerSelectService
{
    use HandlerSelectServiceTrait;

    /**
     * 获取办理人权限设置列表tabs页签，如：用户、角色和部门等，可以返回其中一种或者多种，按业务需求决定
     *
     * @return array<string> tabs页签
     */
    public function getHandlerType(): array
    {
//        return ["用户", "角色", "部门"];
        return ["用户", "角色"];
    }

    /**
     * 获取用户列表、角色列表、部门列表等，可以返回其中一种或者多种，按业务需求决定
     *
     * @param HandlerQuery $query 查询参数
     * @return HandlerSelectVo 结果
     */
    public function getHandlerSelect(HandlerQuery $query): HandlerSelectVo
    {
        if ($query->getHandlerType() == "角色") {
            return $this->getRole($query);
        }
//
//        if ($query->getHandlerType() == "部门") {
//            return $this->getDept($query);
//        }

        if ($query->getHandlerType() == "用户") {
            return $this->getUser($query);
        }

        return new HandlerSelectVo();
    }

    /**
     * 办理人权限名称回显，兼容老项目，新项目重写提高性能
     *
     * @param array<string> $storageIds 入库主键集合
     * @return array<HandlerFeedBackVo> 结果
     */
    public function handlerFeedbackBak(array $storageIds): array
    {
        $handlerFeedBackVos = [];
        if (empty($storageIds)) {
            return $handlerFeedBackVos;
        }

        // 分类存储不同类型的ID
        $roleIds = [];
        $deptIds = [];
        $userIds = [];

        foreach ($storageIds as $storageId) {
            if (str_starts_with($storageId, 'role:')) {
                $roleIds[] = substr($storageId, 5);
            } elseif (str_starts_with($storageId, 'dept:')) {
                $deptIds[] = substr($storageId, 5);
            } else {
                $userIds[] = $storageId;
            }
        }

        // 构建映射关系
        $authMap = [];

        // 处理角色
        if (!empty($roleIds)) {
            $roleList = [
                (object)['roleId' => 1, 'roleName' => '超级管理员'],
                (object)['roleId' => 2, 'roleName' => '普通用户'],
                (object)['roleId' => 3, 'roleName' => '部门经理'],
                (object)['roleId' => 4, 'roleName' => '财务人员'],
                (object)['roleId' => 5, 'roleName' => '人事专员']
            ];

            foreach ($roleList as $role) {
                if (in_array((string)$role->roleId, $roleIds)) {
                    $authMap['role:' . $role->roleId] = $role->roleName;
                }
            }
        }
//
//        // 处理部门
//        if (!empty($deptIds)) {
//            $deptList = [
//                (object)['deptId' => 1, 'deptName' => '技术部'],
//                (object)['deptId' => 2, 'deptName' => '市场部'],
//                (object)['deptId' => 3, 'deptName' => '财务部'],
//                (object)['deptId' => 4, 'deptName' => '人力资源部'],
//                (object)['deptId' => 5, 'deptName' => '运营部']
//            ];
//
//            foreach ($deptList as $dept) {
//                if (in_array((string)$dept->deptId, $deptIds)) {
//                    $authMap['dept:' . $dept->deptId] = $dept->deptName;
//                }
//            }
//        }

        // 处理用户
        if (!empty($userIds)) {
            $userList = [
                (object)['userId' => 1, 'nickName' => '超级管理员'],
                (object)['userId' => 2, 'nickName' => '张三'],
                (object)['userId' => 3, 'nickName' => '李四'],
                (object)['userId' => 4, 'nickName' => '王五'],
                (object)['userId' => 5, 'nickName' => '赵六']
            ];

            foreach ($userList as $user) {
                if (in_array((string)$user->userId, $userIds)) {
                    $authMap[(string)$user->userId] = $user->nickName;
                }
            }
        }

        // 按照原始顺序构建返回结果
        foreach ($storageIds as $storageId) {
            $handlerFeedBackVos[] = new HandlerFeedBackVo($storageId, $authMap[$storageId] ?? '');
        }

        return $handlerFeedBackVos;
    }

    /**
     * 获取角色列表
     *
     * @param HandlerQuery $query
     * @return HandlerSelectVo
     */
    private function getRole(HandlerQuery $query): HandlerSelectVo
    {
        // 构造假的角色数据
        $roleList = Role::get()->all(); // 使用all返回外面是数组,里面是Role Model对象的数组
        // 构建HandlerFunDto
        $handlerFunDto = (new HandlerFunDto($roleList, count($roleList)))
            ->setStorageId(function ($role) {
                return 'role:' . $role->id;
            })
            ->setHandlerCode(function ($role) {
                return $role->name;
            })
            ->setHandlerName(function ($role) {
                return $role->name;
            })
            ->setCreateTime(function ($role) {
                return $role->created_at;
            });

        // 调用方法返回结果
        return $this->getHandlerSelectVo($handlerFunDto);
    }

    /**
     * 获取部门列表--webman admin 没有部门,不使用,保留,假数据
     *
     * @param HandlerQuery $query
     * @return HandlerSelectVo
     */
    private function getDept(HandlerQuery $query): HandlerSelectVo
    {
        // 构造假的部门数据
        $deptList = [
            (object)[
                'deptId' => 1,
                'deptName' => '技术部',
                'createTime' => '2024-01-01 00:00:00'
            ],
            (object)[
                'deptId' => 2,
                'deptName' => '市场部',
                'createTime' => '2024-01-02 00:00:00'
            ],
            (object)[
                'deptId' => 3,
                'deptName' => '财务部',
                'createTime' => '2024-01-03 00:00:00'
            ],
            (object)[
                'deptId' => 4,
                'deptName' => '人力资源部',
                'createTime' => '2024-01-04 00:00:00'
            ],
            (object)[
                'deptId' => 5,
                'deptName' => '运营部',
                'createTime' => '2024-01-05 00:00:00'
            ]
        ];

        // 构建HandlerFunDto
        $handlerFunDto = (new HandlerFunDto($deptList, count($deptList)))
            ->setStorageId(function ($dept) {
                return 'dept:' . $dept->deptId;
            })
            ->setHandlerName(function ($dept) {
                return $dept->deptName;
            })
            ->setCreateTime(function ($dept) {
                return $dept->createTime;
            });

        // 调用方法返回结果
        return $this->getHandlerSelectVo($handlerFunDto);
    }

    /**
     * 获取用户列表
     *
     * @param HandlerQuery $query
     * @return HandlerSelectVo
     */
    private function getUser(HandlerQuery $query): HandlerSelectVo
    {
        // 构造假的用户数据
        $userList = Admin::get()->all();
        $deptList = [];
        // 构造假的部门数据
        // $deptList = [
        //     (object)[
        //         'deptId' => 1,
        //         'deptName' => '技术部',
        //         'parentId' => 0
        //     ],
        //     (object)[
        //         'deptId' => 2,
        //         'deptName' => '市场部',
        //         'parentId' => 0
        //     ],
        //     (object)[
        //         'deptId' => 3,
        //         'deptName' => '财务部',
        //         'parentId' => 0
        //     ],
        //     (object)[
        //         'deptId' => 4,
        //         'deptName' => '前端组',
        //         'parentId' => 1
        //     ],
        //     (object)[
        //         'deptId' => 5,
        //         'deptName' => '后端组',
        //         'parentId' => 1
        //     ]
        // ];

        // 构建HandlerFunDto
        $handlerFunDto = (new HandlerFunDto($userList, count($userList)))
            ->setStorageId(function ($user) {
                return (string)$user->id;
            })
            ->setHandlerCode(function ($user) {
                return $user->username;
            })
            ->setHandlerName(function ($user) {
                return $user->nickname;
            })
            ->setCreateTime(function ($user) {
                return $user->created_at;
            })
            ->setGroupName(function ($user) {
                return $user->dept ? $user->dept->deptName : '';
            });

        // 构建TreeFunDto
        $treeFunDto = (new TreeFunDto($deptList))
            ->setId(function ($dept) {
                return (string)$dept->deptId;
            })
            ->setName(function ($dept) {
                return $dept->deptName;
            })
            ->setParentId(function ($dept) {
                return (string)$dept->parentId;
            });

        // 调用带树结构的方法返回结果
        return $this->getHandlerSelectVoWithTree($handlerFunDto, $treeFunDto);
    }
}
