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
        <style>
            table tr td{
                overflow-wrap: anywhere;
            }
        </style>
    </head>
    
    <body>
        <div class="account-page">
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
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 mt-3">
                            <section class="page-content">
                                <section class="profile" id="new-report">
                                    <h4 class=" font-weight-bold mt-3 mb-2">شكوى جديدة</h4>
                                    <div class="container">
                                        <form role="form">
                                            <div class="form-group">
                                                <label for="reportTitle">عنوان الشكوى:</label>
                                                <input type="text" class="form-control" id="reportTitle" placeholder="إدخل عنوان معبر عن الشكوى هنا...">
                                            </div>
                                            <div class="form-group">
                                                <label for="reportDescription">تفاصيل الشكوى:</label>
                                                <textarea id="reportDescription" style="height: 100px;" class="form-control" placeholder="إدخل كل التفاصيل المتعلقة بالشكوى هنا لمساعدتنا في حلها في أسرع وقت"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <button class="btn btn-info grad-btn" id="submitReport" type="button">إرسال الشكوى</button>
                                            </div>
                                        </form>
                                    </div>
                                </section>
                                <hr>
                                <section class="purches" id="reports">
                                    <h4 class=" font-weight-bold mt-3 mb-2">الشكاوى الخاصة بي</h4>
                                    <div class="container">
                                        <table class="table table-striped table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>م</th>
                                                    <th>عنوان الشكوى</th>
                                                    <th>كود الشكوى</th>
                                                    <th>تاريخ الإنشاء</th>
                                                    <th>الحالة</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $reports = my_reports($conn);
                                            if ($reports['error']) {
                                                sendError($reports['message']);
                                            } else {
                                                if ($reports['num'] == 0) {
                                                    emptyRow(5, "لا يوجد لديك شكاوى حتى الآن");
                                                } else {
                                                    $n = 1;
                                                    foreach ($reports['reports'] as $report_item) {
                                                        report_item($n, $report_item);
                                                        $n++;
                                                    }
                                                }
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </section>
                            </section>
    
                        </div>
                        <div class="col-md-4">
                            <?php include 'sidebar.php'; ?>
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
                // Shifting tabs content
                $("#submitReport").click(()=>{
                    var title = $("#reportTitle").val(),
                    description = $("#reportDescription").val();
                    
                    $.post("../inc/run.php", {target: 'new-report', title: title, description: description}, (data)=>{
                        var data = JSON.parse(data);
                        if(data.error){
                            notiModal(`<i class="fas fa-times text-danger ml-2"></i> ${data.message}`);
                        }else{
                            notiModal(`<i class="fas fa-check text-success ml-2"></i> ${data.message}`);
                        }
                    })
                })
            })
        </script>
    </body>
    
    </html>
    <?php
}