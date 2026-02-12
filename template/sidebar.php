<?php 
    include 'connection/connection.php';
    
?>
<?php 
    $id_user = $_SESSION['user_id'];
    $level = $_SESSION['level'];
    $role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;
    $list_user = mysqli_query($conn, "SELECT * FROM mst_user WHERE id_user = '$id_user' LIMIT 1");
    
    // Initialize Permission for current user
    $userPermission = null;
    if (class_exists('Permission') && $conn && $role_id) {
        $userPermission = new Permission($conn, $role_id, $level);
    }
?>
    <?php foreach($list_user as $lu):?>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="core/<?= $lu['image']?>" width="80" height="80"/>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="block m-t-xs font-bold"><?= $lu['nama']?></span>
                        <span class="text-muted text-xs block"><?= $lu['level']?> <b class="caret"></b></span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="confirmLogout()">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    IN+
                </div>
            </li>
            <li class="active">
                <a href="dashboard.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboards</span> </a>
            </li>
            <?php if($lu['level'] == 'SuperAdmin'):?>
            <li>
                <a href="list_role.php"><i class="fa fa-users"></i> <span class="nav-label">Role</span> </a>
            </li>
            <li>
                <a href="user.php"><i class="fa fa-user"></i> <span class="nav-label">User</span> </a>
            </li>
            <li>
                <a href="menu-submenu.php"><i class="fa fa-bars"></i> <span class="nav-label">Menu</span> </a>
            </li>
            <li>
                <a href="access_menu.php"><i class="fa fa-lock"></i> <span class="nav-label">Access Menu</span> </a>
            </li>
            <li>
                <a href="#"><i class="fa fa-cogs"></i> <span class="nav-label">Generators</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li><a href="generator_table.php"><i class="fa fa-database"></i> Table Manager</a></li>
                    <li><a href="generator_crud.php"><i class="fa fa-code"></i> CRUD Generator</a></li>
                </ul>
            </li>
            <?php endif;?>
            
            <!-- Dynamic menu based on role_permissions -->
            <?php 
                // Get menus from user_access_menu joined with permissions
                $query = "SELECT DISTINCT 
                            um.id as menu_id,
                            um.menu, 
                            um.icon,
                            usm.id as submenu_id,
                            usm.title as submenu_title,
                            LOWER(REPLACE(COALESCE(usm.title, um.menu), ' ', '_')) as module_name
                          FROM user_access_menu uam
                          JOIN user_menu um ON um.id = uam.menu_id
                          LEFT JOIN user_sub_menu usm ON usm.menu_id = um.id AND usm.is_active = 1
                          WHERE uam.role_id = ?
                          ORDER BY um.menu, usm.title";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $role_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $menus = [];
                while ($row = $result->fetch_assoc()) {
                    $menuName = $row['menu'];
                    $moduleName = $row['module_name'];
                    
                    // Check permission for this module
                    $hasViewPermission = true; // Default allow if Permission class not available
                    if ($userPermission !== null) {
                        $hasViewPermission = $userPermission->can($moduleName, 'view');
                    }
                    
                    if ($hasViewPermission) {
                        if (!isset($menus[$menuName])) {
                            $menus[$menuName] = [
                                'icon' => $row['icon'],
                                'submenus' => []
                            ];
                        }
                        
                        if ($row['submenu_title']) {
                            $menus[$menuName]['submenus'][] = [
                                'title' => $row['submenu_title'],
                                'url' => strtolower(str_replace(' ', '_', $row['submenu_title'])) . '.php'
                            ];
                        }
                    }
                }
                $stmt->close();
            ?>
            
            <?php foreach ($menus as $menuName => $menuData) : ?>
                <?php if (empty($menuData['submenus'])) : ?>
                    <!-- Menu without submenus -->
                    <li>
                        <a href="<?= strtolower(str_replace(' ', '_', $menuName)) ?>.php">
                            <i class="<?= $menuData['icon'] ?: 'fa fa-folder' ?>"></i>
                            <span class="nav-label"><?= htmlspecialchars($menuName) ?></span>
                        </a>
                    </li>
                <?php else : ?>
                    <!-- Menu with submenus -->
                    <li>
                        <a href="#">
                            <i class="<?= $menuData['icon'] ?: 'fa fa-folder' ?>"></i>
                            <span class="nav-label"><?= htmlspecialchars($menuName) ?></span>
                            <span class="fa arrow"></span>
                        </a>
                        <ul class="nav nav-second-level collapse">
                            <?php foreach ($menuData['submenus'] as $submenu) : ?>
                                <li><a href="<?= $submenu['url'] ?>"><?= htmlspecialchars($submenu['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            <!-- end dynamic menu -->
        </ul>

    </div>
</nav>
<?php endforeach; ?>