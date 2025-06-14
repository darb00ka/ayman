<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id']){
    header("Location: ../login");
}else{
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <?php include 'head.php'?>
    </head>
    
    <body>
        <div class="new-order-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
                <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">تنبيه</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="notiBody">
    
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Noti Modal -->
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container mt-3">
                                <section class="page-content rounded">
                                    <h4>
                                        <i class="fas fa-plus text-info ml-2"></i>
                                        معامله جديده
                                    </h4>
                                    <hr>
                                    <form role="from">
                                        <div class="form-group">
                                            <label for="title">عنوان الطلب:</label>
                                            <input type="text" id="title" class="form-control"
                                                placeholder="إدخل عنوان يعبر عن الطلب هنا">
                                        </div>
                                        <div class="form-group">
                                            <?php
                                                $cats = cats($conn);
                                                if($cats['error']){
                                                    sendError($cats['message']);
                                                }else{
                                                    ?>
                                                    <label for="cat">التصنيف:</label>
                                                    <select id="cat" class="form-control">
                                                        <option value="0">إختر التصنيف</option>
                                                        <?php
                                                        if($cats['num'] > 0){
                                                            $cats = $cats['cats'];
                                                            foreach($cats as $cat){
                                                                ?>
                                                                <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                    <?php
                                                }
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="seller">ID البائع:</label>
                                            <input type="number" class="form-control" placeholder="إدخل ID البائع"
                                                id="seller">
                                            <span id="sellerError" class="text-danger"></span>
                                            <div id="sellerData">
                                                إسم البائع: <span id="sellerName"></span><br>
                                                رقم هاتف البائع: <span id="sellerPhone"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="deadline">عدد أيام العمل <span>(أقصى موعد للتسليم)</span></label>
                                            <input type="number" id="deadline" class="form-control"
                                                placeholder="إختر عدد الأيام التي يجب تسليم العمل خلالها" value="1">
                                        </div>
                                        <div class="form-group">
                                            <label for="warranty">عدد أيام الضمان</label>
                                            <span style="font-size: 0.87em;" class="d-block mb-3">(يتم حساب تلك الأيام من
                                                لحظة التسليم) يمكن ترك هذه الخانة "0 يوم" إن كنت لما تتفق مع البائع على أيام
                                                ضمان</span>
                                            <input type="number" value="0" id="warranty" class="form-control"
                                                placeholder="عدد أيام الضمان بعد التسليم">
                                        </div>
                                        <div class="form-group">
                                            <label for="price">السعر (بالجنيه المصري)</label>
                                            <input type="number" id="price" class="form-control"
                                                placeholder="إدخل سعر الطلب بالجنيه المصري">
                                        </div>
                                        <div class="form-group">
                                            <label for="description">الوصف:</label>
                                            <textarea id="description" class="form-control" style="height: 100px;"
                                                placeholder="يجب كتابة كافة التفاصيل المتفق عليها مع البائع لضمان حقك تماما"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-block btn-info grad-btn" type="button"
                                                id="newOrder">إرسال الطلب</button>
                                        </div>
                                    </form>
                                </section>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php';?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="../js/popper.min.js"></script>
        <script src="../js/ajax.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/aos.js"></script>
        <script src="../js/main.js"></script>
        <script src="../js/notifications.js"></script>
        <script>
        AOS.init();
        $(function() {
            $("#sellerData, #sellerError").hide();
            $("#newOrder").click(function() {
                var title = $("#title").val(),
                    seller = $("#seller").val(),
                    deadline = $("#deadline").val(),
                    warranty = $("#warranty").val(),
                    price = $("#price").val(),
                    cat = $("#cat").val(),
                    description = $("#description").val();
    
                if (!title || !seller || !deadline || !price || !description) {
                    notiModal(`<i class="fas fa-times text-danger ml-2"></i>  يجب ملئ كافة الحقول`);
                } else {
                    if (!$.isNumeric(deadline) || !$.isNumeric(price)) {
                        notiModal(
                            `<i class="fas fa-times text-danger ml-2"></i>  يجب إدخال أرقام فقط في خانات (عدد أيام التسليم، عدد أيام الضمان، السعر)`
                            );
                    } else {
                        $.post('../inc/run.php', {
                            target: 'new-order',
                            title: title,
                            seller: seller,
                            deadline: deadline,
                            warranty: warranty,
                            price: price,
                            cat: cat,
                            description: description
                        }, (data) => {
                            var data = JSON.parse(data);
    
                            if (data.error) {
                                notiModal(
                                    `<i class="fas fa-times text-danger ml-2"></i>${data.message}`
                                    );
                            } else {
                                window.location.href=`order.php?id=${data.id}`;
                            }
                        })
                    }
                }
    
            })
    
            function checkSeller(sellerId) {
                $("#sellerError, #sellerName, #sellerPhone").empty();
                $("#sellerData, #sellerError").hide();
    
                $.post('../inc/run.php', {
                    target: 'check-user',
                    seller: sellerId
                }, (data) => {
                    var data = JSON.parse(data);
                    if (data.error) {
                        $("#sellerError").html(
                            `<i class="fas fa-times text-danger ml-1"></i> ${data.message}`).show();
                    } else {
                        var icon = '';
                        if(data.user.verified == "yes"){
                            icon = `<i class='fas fa-check-circle text-success ml-1 mr-1'></i>`;
                        }
                        var sellerName = icon + ' ' + data.user.first_name + ' ' + data.user.last_name,
                            sellerPhone = data.user.phone;
                        $("#sellerName").html(sellerName);
                        $("#sellerPhone").text(sellerPhone);
                        $("#sellerData").show();
                    }
                })
            }
            $("#seller").keyup(function (params) {
                checkSeller($(this).val());
            })
            $("#seller").change(function (params) {
                checkSeller($(this).val());
            })
        })
        </script>
    </body>
    
    </html>
    <?php
}