<?php
session_start();
include '../inc/func.php';
remember();
if(!$_SESSION['id'] || $_SESSION['acc_type'] != "admin"){
    header("Location: ../login");
}else{
    $balance = balance($conn, $_SESSION['id']);
    $available = $balance['available'];
    $banned = $balance['banned'];
    $tax = tax($conn);
    $tax = $tax['tax'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <?php include 'head.php'?>
    </head>
    
    <body>
        <div class="admin-page">
            <?php include 'header.php' ?>
            <div class="container">
                <!-- Noti Modal -->
                <div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                <div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">رفض تسليم طلب</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="refuseModal">
                                <div class="conainer-fluid">
                                    <form role="form">
                                        <div class="form-group">
                                            <label class="font-weight-bold">سبب الرفض:</label>
                                            <textarea id="refuseReason" style="height: 50px;" class="form-control" placeholder="يرجى شرح كافة الأسباب التي دفعتك لرفض إستلام الطلب"></textarea>
                                        </div>
                                        <div class="form-group mt-3">
                                            <button type="button" class="btn btn-block btn-info grad-btn" id="confirmRefuse">تأكيد عدم الإستلام</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="container mt-3 page-content">
                                <section class="setting">
                                    <h4 class="mb-3">
                                        إنشاء تصنيف جديد
                                    </h4>
                                    <form role="form">
                                        <div class="form-group">
                                            <label for="newTax">إسم التصنيف</label>
                                            <input type="text" placeholder="إدخل إسم التصنيف الذي تريد إنشاءه" id="catName" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success" id="newCat">إنشاء التصنيف</button>
                                        </div>
                                    </form>
                                </section>
                                <section class="mt-3">
                                    <h4 class="mb-3">
                                        إدارة التصنيفات الحالية
                                    </h4>
                                    <table class="table cats text-center">
                                        <thead>
                                            <th>
                                                م
                                            </th>
                                            <th>
                                                إسم التصنيف
                                            </th>
                                            <th>
                                                حذف
                                            </th>
                                        </thead>
                                        <?
                                        $cats = cats($conn);
                                        if($cats['error']){
                                            emptyRow(3, $cats['message']);
                                        }else{
                                            if($cats['num'] == 0){
                                                emptyRow(3, "لا يوجد أي تصنيفات حتى الآن، يمكنك إضافة تصينف جديد من الجزء المخصص في أعلى هذه الصفحة");
                                            }else{
                                                $cats = $cats['cats'];
                                                $n = 1;
                                                foreach($cats as $cat){
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?=$n?>
                                                        </td>
                                                        <td>
                                                            <?=$cat['name']?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-danger delete-cat" type="button" cat="<?=$cat['id']?>"><i class="fas fa-times">   حذف التصنيف</i></button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $n++;
                                                }
                                            }
                                        }
                                        ?>
                                    </table>
                                </section>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php'; ?>
                        </div>
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
                if($(window).width() < 0){
                    window.location.replace("https://wastetco.com");
                }
            $("#newCat").click(()=>{
                catName = $("#catName").val();
                $.post("../inc/run.php", {target: "new-cat", cat: catName}, (data)=>{
                    var data = JSON.parse(data);
                    if(data.error){
                        notiModal(`<i class="fas fa-times text-danger ml-1"></i>${data.message}`);
                    }else{
                        notiModal(`<i class="fas fa-check text-success ml-1"></i>${data.message}`);
                        window.location.href="cat";
                    }
                })
            })
            $(".delete-cat").click(function(){
                var $this = $(this),
                    cat = $this.attr("cat");
                    $.post("../inc/run.php", {target: "delete-cat", cat: cat}, (data)=>{
                    var data = JSON.parse(data);
                    if(data.error){
                        notiModal(`<i class="fas fa-times text-danger ml-1"></i>${data.message}`);
                    }else{
                        notiModal(`<i class="fas fa-check text-success ml-1"></i>${data.message}`);
                        $this.parent().parent().fadeOut(500).remove();
                    }
                })
            })
            })
        </script>
    </body>
    
    </html>
    <?php
}