<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'web'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->group(['prefix' => 'auth'], function () use ($router) {
            $router->post('/login', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:login']);
            $router->post('/register', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:register']);
            $router->post('/reset-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:resetPassword']);
            $router->post('/forgot-password', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:forgotPassword']);
        });

        $router->group(['prefix' => 'user', 'middleware' => 'auth.admin'], function () use ($router) {
            $router->post('/logout', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:logout']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getProfile']);
        });

        $router->group(['prefix' => 'menus'], function () use ($router) {
            $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addMenu']);
            $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteMenu']);
            $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editMenu']);
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findMenu']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getMenu']);
        });

        $router->group(['prefix' => 'menu-items'], function () use ($router) {
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getMenuItem']);
            $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addMenuItem']);
            $router->put('/sort', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkUpdateMenuItemOrder']);
            $router->get('/key', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getMenuItemByKey']);
            $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteMenuItem']);
            $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editMenuItem']);
            $router->put('/{id}/move', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editMenuItemOrder']);
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findMenuItem']);
            $router->get('/{id}/permissions', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getMenuItemPermission']);
            $router->post('/{id}/permissions', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:setMenuItemPermission']);
        });

        $router->group(['prefix' => 'permissions'], function () use ($router) {
            $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addPermission']);
            $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeletePermission']);
            $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deletePermission']);
            $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editPermission']);
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findPermission']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getPermission']);
        });

        $router->group(['prefix' => 'roles'], function () use ($router) {
            $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addRole']);
            $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeleteRole']);
            $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteRole']);
            $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editRole']);
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findRole']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getRole']);
        });

        $router->group(['prefix' => 'role-permissions'], function () use ($router) {
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getRolePermission']);
            $router->post('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addOrEditRolePermission']);
            $router->get('/{id}/all', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getAllPermissionWithRole']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getAllRolePermission']);
        });

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addUser']);
            $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeleteUser']);
            $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteUser']);
            $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editUser']);
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findUser']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getUser']);
        });

        $router->group(['prefix' => 'user-roles'], function () use ($router) {
            $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getUserRole']);
            $router->post('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addOrEditUserRole']);
            $router->get('/{id}/all', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getAllRoleWithUser']);
            $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getAllUserRole']);
        });

        $router->group(['prefix' => 'archery'], function () use ($router) {
            $router->group(['prefix' => 'age-categories'], function () use ($router) {
                $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addArcheryAgeCategory']);
                $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryAgeCategory']);
                $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteArcheryAgeCategory']);
                $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editArcheryAgeCategory']);
                $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findArcheryAgeCategory']);
                $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getArcheryAgeCategory']);
            });
            $router->group(['prefix' => 'categories'], function () use ($router) {
                $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addArcheryCategory']);
                $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryCategory']);
                $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteArcheryCategory']);
                $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editArcheryCategory']);
                $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findArcheryCategory']);
                $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getArcheryCategory']);
            });
            $router->group(['prefix' => 'clubs'], function () use ($router) {
                $router->post('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:addArcheryClub']);
                $router->delete('/bulk', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:bulkDeleteArcheryClub']);
                $router->delete('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:deleteArcheryClub']);
                $router->put('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:editArcheryClub']);
                $router->get('/{id}', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:findArcheryClub']);
                $router->get('/', ['uses' => 'BloCController@execute', 'middleware' => 'bloc:getArcheryClub']);
            });
        });
    });
});
