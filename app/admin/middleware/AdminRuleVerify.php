<?php
/*
 * @Description  : 权限验证中间件
 * @Author       : skyselang 215817969@qq.com
 * @Date         : 2020-04-25
 */

namespace app\admin\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Config;
use app\admin\cache\AdminUserCache;

class AdminRuleVerify
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $app_name = app('http')->getName();
        $pathinfo = $request->pathinfo();
        $rule_url = $app_name . '/' . $pathinfo;
        $rule_white_list = Config::get('admin.rule_white_list');

        if (!in_array($rule_url, $rule_white_list)) {
            $admin_user_id = $request->header('Admin-User-Id', '');
            $super_admin = Config::get('admin.super_admin');
            if (!in_array($admin_user_id, $super_admin)) {
                $admin_user = AdminUserCache::get($admin_user_id);
                if (empty($admin_user)) {
                    return error('登录失效，请重新登录', 401);
                }

                if ($admin_user['is_prohibit'] == 1) {
                    return error('账号已被禁用', 401);
                }

                if (!in_array($rule_url, $admin_user['roles'])) {
                    return error('你没有权限操作', '', ['rule_url' => $rule_url]);
                }
            }
        }

        return $next($request);
    }
}