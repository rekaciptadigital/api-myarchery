<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\BLoC\Web\Admin\AddAdmin;
use App\BLoC\Web\Admin\BulkDeleteAdmin;
use App\BLoC\Web\Admin\DeleteAdmin;
use App\BLoC\Web\Admin\EditAdmin;
use App\BLoC\Web\Admin\FindAdmin;
use App\BLoC\Web\Admin\GetAdmin;
use App\BLoC\Web\AdminRole\AddOrEditAdminRole;
use App\BLoC\Web\AdminRole\GetAllRoleWithAdmin;
use App\BLoC\Web\AdminRole\GetAllAdminRole;
use App\BLoC\Web\AdminRole\GetAdminRole;
use App\BLoC\Web\AdminAuth\ForgotPassword;
use App\BLoC\Web\AdminAuth\Login;
use App\BLoC\Web\AdminAuth\Register;
use App\BLoC\Web\AdminAuth\ResetPassword;
use App\BLoC\Web\AdminAuth\GetProfile;
use App\BLoC\Web\AdminAuth\Logout;
use App\BLoC\Web\ArcheryAgeCategory\EditArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\FindArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\DeleteArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\BulkDeleteArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\GetArcheryAgeCategory;
use App\BLoC\Web\ArcheryAgeCategory\AddArcheryAgeCategory;
use App\BLoC\Web\ArcheryCategory\DeleteArcheryCategory;
use App\BLoC\Web\ArcheryCategory\BulkDeleteArcheryCategory;
use App\BLoC\Web\ArcheryCategory\FindArcheryCategory;
use App\BLoC\Web\ArcheryCategory\AddArcheryCategory;
use App\BLoC\Web\ArcheryCategory\EditArcheryCategory;
use App\BLoC\Web\ArcheryCategory\GetArcheryCategory;
use App\BLoC\Web\ArcheryClub\BulkDeleteArcheryClub;
use App\BLoC\Web\ArcheryClub\FindArcheryClub;
use App\BLoC\Web\ArcheryClub\DeleteArcheryClub;
use App\BLoC\Web\ArcheryClub\EditArcheryClub;
use App\BLoC\Web\ArcheryClub\AddArcheryClub;
use App\BLoC\Web\ArcheryClub\GetArcheryClub;
use App\BLoC\Web\Menu\AddMenu;
use App\BLoC\Web\Menu\DeleteMenu;
use App\BLoC\Web\Menu\EditMenu;
use App\BLoC\Web\Menu\FindMenu;
use App\BLoC\Web\Menu\GetMenu;
use App\BLoC\Web\MenuItem\AddMenuItem;
use App\BLoC\Web\MenuItem\BulkUpdateMenuItemOrder;
use App\BLoC\Web\MenuItem\DeleteMenuItem;
use App\BLoC\Web\MenuItem\EditMenuItem;
use App\BLoC\Web\MenuItem\EditMenuItemOrder;
use App\BLoC\Web\MenuItem\FindMenuItem;
use App\BLoC\Web\MenuItem\GetMenuItem;
use App\BLoC\Web\MenuItem\GetMenuItemByKey;
use App\BLoC\Web\MenuItemPermission\GetMenuItemPermission;
use App\BLoC\Web\MenuItemPermission\SetMenuItemPermission;
use App\BLoC\Web\Permission\AddPermission;
use App\BLoC\Web\Permission\BulkDeletePermission;
use App\BLoC\Web\Permission\DeletePermission;
use App\BLoC\Web\Permission\EditPermission;
use App\BLoC\Web\Permission\FindPermission;
use App\BLoC\Web\Permission\GetPermission;
use App\BLoC\Web\Role\AddRole;
use App\BLoC\Web\Role\BulkDeleteRole;
use App\BLoC\Web\Role\DeleteRole;
use App\BLoC\Web\Role\EditRole;
use App\BLoC\Web\Role\FindRole;
use App\BLoC\Web\Role\GetRole;
use App\BLoC\Web\RolePermission\AddOrEditRolePermission;
use App\BLoC\Web\RolePermission\GetAllPermissionWithRole;
use App\BLoC\Web\RolePermission\GetAllRolePermission;
use App\BLoC\Web\RolePermission\GetRolePermission;
use App\BLoC\Web\User\AddUser;
use App\BLoC\Web\User\BulkDeleteUser;
use App\BLoC\Web\User\DeleteUser;
use App\BLoC\Web\User\EditUser;
use App\BLoC\Web\User\FindUser;
use App\BLoC\Web\User\GetUser;

class WebServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService("AddAdmin", AddAdmin::class);
        $this->registerService("bulkDeleteAdmin", BulkDeleteAdmin::class);
        $this->registerService("deleteAdmin", DeleteAdmin::class);
        $this->registerService("editAdmin", EditAdmin::class);
        $this->registerService("findAdmin", FindAdmin::class);
        $this->registerService("getAdmin", GetAdmin::class);
        $this->registerService("forgotPassword", ForgotPassword::class);
        $this->registerService("login", Login::class);
        $this->registerService("register", Register::class);
        $this->registerService("resetPassword", ResetPassword::class);
        $this->registerService("getProfile", GetProfile::class);
        $this->registerService("logout", Logout::class);
        $this->registerService("addMenu", AddMenu::class);
        $this->registerService("deleteMenu", DeleteMenu::class);
        $this->registerService("editMenu", EditMenu::class);
        $this->registerService("findMenu", FindMenu::class);
        $this->registerService("getMenu", GetMenu::class);
        $this->registerService("addMenuItem", AddMenuItem::class);
        $this->registerService("bulkUpdateMenuItemOrder", BulkUpdateMenuItemOrder::class);
        $this->registerService("deleteMenuItem", DeleteMenuItem::class);
        $this->registerService("editMenuItem", EditMenuItem::class);
        $this->registerService("editMenuItemOrder", EditMenuItemOrder::class);
        $this->registerService("findMenuItem", FindMenuItem::class);
        $this->registerService("getMenuItem", GetMenuItem::class);
        $this->registerService("getMenuItemByKey", GetMenuItemByKey::class);
        $this->registerService("getMenuItemPermission", GetMenuItemPermission::class);
        $this->registerService("setMenuItemPermission", SetMenuItemPermission::class);
        $this->registerService("addPermission", AddPermission::class);
        $this->registerService("bulkDeletePermission", BulkDeletePermission::class);
        $this->registerService("deletePermission", DeletePermission::class);
        $this->registerService("editPermission", EditPermission::class);
        $this->registerService("findPermission", FindPermission::class);
        $this->registerService("getPermission", GetPermission::class);
        $this->registerService("addRole", AddRole::class);
        $this->registerService("bulkDeleteRole", BulkDeleteRole::class);
        $this->registerService("deleteRole", DeleteRole::class);
        $this->registerService("editRole", EditRole::class);
        $this->registerService("findRole", FindRole::class);
        $this->registerService("getRole", GetRole::class);
        $this->registerService("addOrEditRolePermission", AddOrEditRolePermission::class);
        $this->registerService("getAllPermissionWithRole", GetAllPermissionWithRole::class);
        $this->registerService("getAllRolePermission", GetAllRolePermission::class);
        $this->registerService("getRolePermission", GetRolePermission::class);
        $this->registerService("addUser", AddUser::class);
        $this->registerService("bulkDeleteUser", BulkDeleteUser::class);
        $this->registerService("deleteUser", DeleteUser::class);
        $this->registerService("editUser", EditUser::class);
        $this->registerService("findUser", FindUser::class);
        $this->registerService("getUser", GetUser::class);
        $this->registerService("addOrEditAdminRole", AddOrEditAdminRole::class);
        $this->registerService("getAllRoleWithAdmin", GetAllRoleWithAdmin::class);
        $this->registerService("getAllAdminRole", GetAllAdminRole::class);
        $this->registerService("getAdminRole", GetAdminRole::class);
        $this->registerService("editArcheryAgeCategory", EditArcheryAgeCategory::class);
        $this->registerService("findArcheryAgeCategory", FindArcheryAgeCategory::class);
        $this->registerService("deleteArcheryAgeCategory", DeleteArcheryAgeCategory::class);
        $this->registerService("bulkDeleteArcheryAgeCategory", BulkDeleteArcheryAgeCategory::class);
        $this->registerService("getArcheryAgeCategory", GetArcheryAgeCategory::class);
        $this->registerService("addArcheryAgeCategory", AddArcheryAgeCategory::class);
        $this->registerService("deleteArcheryCategory", DeleteArcheryCategory::class);
        $this->registerService("bulkDeleteArcheryCategory", BulkDeleteArcheryCategory::class);
        $this->registerService("findArcheryCategory", FindArcheryCategory::class);
        $this->registerService("addArcheryCategory", AddArcheryCategory::class);
        $this->registerService("editArcheryCategory", EditArcheryCategory::class);
        $this->registerService("getArcheryCategory", GetArcheryCategory::class);
        $this->registerService("bulkDeleteArcheryClub", BulkDeleteArcheryClub::class);
        $this->registerService("findArcheryClub", FindArcheryClub::class);
        $this->registerService("deleteArcheryClub", DeleteArcheryClub::class);
        $this->registerService("editArcheryClub", EditArcheryClub::class);
        $this->registerService("addArcheryClub", AddArcheryClub::class);
        $this->registerService("getArcheryClub", GetArcheryClub::class);
    }

    private function registerService($serviceName, $className)
    {
        $this->app->singleton($serviceName, function () use ($className) {
            return new $className;
        });
    }
}
