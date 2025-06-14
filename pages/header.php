<div class='header'>
    <div class='container'>
        <nav class='navbar navbar-expand-lg navbar-light'>

            <a href='account' class='navbar-brand'>
                <img src='../img/logo.webp' alt=' class='img-fluid'>
            </a>
            <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
                <span class='navbar-toggler-icon'></span>
            </button>
            <div class='collapse navbar-collapse' id='navbarSupportedContent'>
                <ul class='navbar-nav ml-auto'>
                    <?php
                        if($_SESSION['acc_type'] == "admin"){
                            ?>
                            <li class='nav-item'>
                                <a class='nav-link' href='account'><i class="fas fa-user ml-1"></i><?= short_name($_SESSION) ?> (<?= $_SESSION['id'] ?>)</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link text-center' id="notifications-link" href='notifications'><i class="fas fa-bell ml-2"></i>الإشعارات</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='admin'><i class="fas fa-tachometer-alt ml-1"></i>لوحة التحكم</a>
                            </li>
                            <li class="nav-item">
                                <a id="logout" class="nav-link">تسجيل الخروج<i class="fas fa-sign-out-alt mr-2"></i></a>
                            </li>
                            <?php
                        }else{
                            ?>
                            <li class='nav-item'>
                                <a class='nav-link' href='account'><i class="fas fa-user ml-1"></i><?= short_name($_SESSION) ?> [<?= $_SESSION['id'] ?>]</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link text-center' id="notifications-link" href='notifications'><i class="fas fa-bell ml-2"></i>الإشعارات</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='new_order'><i class="fas fa-plus ml-2"></i> إنشاء معاملة</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='my_orders'><i class="fas fa-shopping-cart ml-1"></i>الطلبات</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='account'><i class="fas fa-money-bill-alt ml-1"></i>الرصيد</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link' href='support'><i class="fas fa-question-circle ml-1"></i>الدعم</a>
                            </li>
                            <li class="nav-item">
                                <a id="logout" class="nav-link">تسجيل الخروج<i class="fas fa-sign-out-alt mr-2"></i></a>
                            </li>
                            <?php
                        }
                        ?>
                </ul>
            </div>
        </nav>
    </div>
</div>