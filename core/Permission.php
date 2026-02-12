<?php
/**
 * Permission Helper Class
 * Handles role-based permission checks for modules
 */
class Permission
{
    private $conn;
    private $roleId;
    private $userLevel;
    private $permissions = [];
    private $hasAnyPermissions = false; // Track if any permissions are set for this role

    public function __construct($conn, $roleId, $userLevel = null)
    {
        $this->conn = $conn;
        $this->roleId = $roleId;
        $this->userLevel = $userLevel;
        $this->loadPermissions();
    }

    /**
     * Load all permissions for the current role
     */
    private function loadPermissions()
    {
        if (!$this->roleId || !$this->conn) {
            return;
        }

        $stmt = $this->conn->prepare("SELECT module_name, can_view, can_create, can_update, can_delete FROM role_permissions WHERE role_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $this->roleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $this->hasAnyPermissions = true; // At least one permission record exists
                $this->permissions[$row['module_name']] = [
                    'view' => (bool)$row['can_view'],
                    'create' => (bool)$row['can_create'],
                    'update' => (bool)$row['can_update'],
                    'delete' => (bool)$row['can_delete']
                ];
            }
            $stmt->close();
        }
    }

    /**
     * Check if role has any permissions configured
     * @return bool
     */
    public function hasPermissionsConfigured()
    {
        return $this->hasAnyPermissions;
    }

    /**
     * Check if user has permission for a specific action on a module
     * SuperAdmin always has full access
     * If no permissions are configured for this role, allow all (legacy fallback)
     * If a specific module is not in permissions, allow access (use user_access_menu as primary control)
     * 
     * @param string $module Module name (e.g., 'users', 'products')
     * @param string $action Action type: 'view', 'create', 'update', 'delete'
     * @return bool
     */
    public function hasPermission($module, $action)
    {
        // SuperAdmin has all permissions
        if ($this->userLevel === 'SuperAdmin') {
            return true;
        }

        // If no permissions configured for this role, allow all (legacy fallback)
        // This ensures existing roles without permissions still work
        if (!$this->hasAnyPermissions) {
            return true;
        }

        $module = strtolower($module);
        $action = strtolower($action);

        // If module not in permissions list, ALLOW access by default
        // This allows menus from user_access_menu to show even without explicit role_permissions
        // Only deny if explicitly configured with can_XXX = false
        if (!isset($this->permissions[$module])) {
            return true; // Default to allow for unconfigured modules
        }

        return isset($this->permissions[$module][$action]) && $this->permissions[$module][$action];
    }

    /**
     * Alias for hasPermission
     */
    public function can($module, $action)
    {
        return $this->hasPermission($module, $action);
    }

    /**
     * Check if user can perform any of the given actions on a module
     * 
     * @param string $module Module name
     * @param array $actions Array of actions to check
     * @return bool
     */
    public function canAny($module, $actions)
    {
        foreach ($actions as $action) {
            if ($this->hasPermission($module, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user can perform all given actions on a module
     * 
     * @param string $module Module name
     * @param array $actions Array of actions to check
     * @return bool
     */
    public function canAll($module, $actions)
    {
        foreach ($actions as $action) {
            if (!$this->hasPermission($module, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for current role
     * 
     * @return array
     */
    public function getAllPermissions()
    {
        return $this->permissions;
    }

    /**
     * Static method to get available modules from database
     * Combines user_menu and user_sub_menu
     * 
     * @param mysqli $conn Database connection
     * @return array
     */
    public static function getAvailableModules($conn)
    {
        $modules = [];

        // Get from user_sub_menu (actual pages)
        $result = $conn->query("SELECT DISTINCT LOWER(REPLACE(title, ' ', '_')) as module_name, title as display_name FROM user_sub_menu WHERE is_active = 1 ORDER BY title");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $modules[$row['module_name']] = $row['display_name'];
            }
        }

        // Also get standalone modules from user_menu that don't have submenus
        $result = $conn->query("SELECT LOWER(REPLACE(menu, ' ', '_')) as module_name, menu as display_name FROM user_menu um WHERE NOT EXISTS (SELECT 1 FROM user_sub_menu usm WHERE usm.menu_id = um.id) ORDER BY menu");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $modules[$row['module_name']] = $row['display_name'];
            }
        }

        return $modules;
    }
}
