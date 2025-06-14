<footer class='mt-4'>
    <div class='container'>
        <div class='row'>
            <div class='col-sm-4'>
                <a href="https://www.wastetco.com/aboutus" class="text-light font-weight-bold lead">من نحن</a>
                <p>
                    موقع واسطيتكو هو موقع خدمي وسيط بين البائع والمشترى يعمل على ضمان حقوق الطرفين فى جميع المعاملات
                    المالية
                    الالكترونية الكبيره والصغيره
                </p>
            </div>
            <div class='col-sm-4'>
                <h3>روابط هامة:</h3>
                <ul>
                <?php
                    // تم التعديل هنا: التحقق من وجود $_SESSION['id'] قبل استخدامه
                    if(!isset($_SESSION['id']) || empty($_SESSION['id'])){ 
                        ?>
                        <li><a href='https://www.wastetco.com/index'>الرئيسية</a></li>
                        <li><a href='https://www.wastetco.com/signup'>إنشاء حساب</a></li>
                        <li><a href='https://www.wastetco.com/payment'>وسائل الدفع</a></li>
                        <li><a href='https://www.wastetco.com/login'>تسجيل الدخول</a></li>
                        <li><a href='https://www.wastetco.com/terms'>إتفاقية الإستخدام والخصوصية</a></li>
                        <li><a href='https://www.wastetco.com/index#contactUs'>تواصل معنا</a></li>
                        <?php 
                    }else{
                        ?>
                        <li><a href='https://www.wastetco.com/index'>الرئيسية</a></li>
                        <li><a href='https://www.wastetco.com/payment'>وسائل الدفع</a></li>
                        <li><a href='https://www.wastetco.com/terms'>شروط الخدمة</a></li>
                        <li><a href='https://www.wastetco.com/privacy'>إتفاقية الخصوصية</a></li>
                        <li><a href='https://www.wastetco.com/pages/support'>تواصل معنا</a></li>
                        <?php
                    }
                ?>
                </ul>
            </div>
            <div class='col-sm-4'>
                <h3>تابعنا:</h3>
                <ul class='list-unstyled'>
                    <li>
                        <a href='https://www.facebook.com/wastetco.official'>
                            <i class='fab fa-facebook ml-2'></i>
                            فيس بوك
                        </a>
                    </li>
                    <li>
                        <a href='https://www.twitter.com/wastetco'>
                            <i class='fab fa-twitter ml-2'></i>
                            تويتر
                        </a>
                    </li>
                    <li>
                        <a href='https://www.instagram.com/wastetco'>
                            <i class='fab fa-instagram ml-2'></i>
                            إنستجرام
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class='text-center mt-3 bg-dark pt-3 pb-1'>
        <div class='container'>
            <div class='row'>
                <div class='col-sm-12'>
                    <p>كافة حقوق المحتوى محفوظة &copy <a href='index' class='font-weight-bold'>واسطيتكو -
                            Wastetco</a></p>
                </div>
                

            </div>
        </div>
    </div>
</footer>