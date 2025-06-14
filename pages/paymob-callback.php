<?php 
include '../inc/func.php';


function encrypt_decrypt($string, $action = 'enc')
{
    $encrypt_method = "AES-256-CBC";
    $secret_key = '7sdfsd4545ewfw7865hfcr'; // user define private key
    $secret_iv = 'fdsfsdf654644sfsdf'; // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    if ($action == 'enc') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'dec') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}
function getOrderBy($key , $value , $valueType){
    global $conn ; 
    $key = safe($key , "string");
    $value = safe($value , $valueType);
    $back = ["error" => 1 ]; 
    if ( ($result  = $conn->query("select  * from  payment_orders   where  `$key` = $value " )) && (mysqli_num_rows($result) > 0 )){
        $back["error"] = 0 ;
        $back = array_merge($back , $result->fetch_assoc() );
    }
    return $back ; 
}
$data = [] ;
$status = false ;
if ( strtolower($_SERVER['REQUEST_METHOD'] )=== "post"){
    $data = json_decode(file_get_contents("php://input") , true);
}else{
    $data = $_GET ;
}


$trans_id = $data["id"] ?? false;
$order_id = ( $data["order"]["id"] ?? !is_array($data["order"]) ? $data["order"] : false )  ;
$value = $data["amount_cents"] ?? false ;
$success = ( !filter_var(   $data["pending"], FILTER_VALIDATE_BOOLEAN) ) && (filter_var(   $data["success"], FILTER_VALIDATE_BOOLEAN) );
$int_id = $data["integration_id"] ?? false ;
if($order_id && $value  && $int_id && $trans_id){
   $order = getOrderBy("order_id" , $order_id , "int");
    if( ( $success) &&  ($order["error"] == 0) && ( ($order["trans_id"] == $trans_id ) || ( $order["trans_id"] == $int_id ) ) && (intval($value) == intval($order["amount"]))  ){

        $status = true ;
        
        // var_dump($admins);
        // var_dump(balance($conn , $order["user_id"]));
        // die();
        if ($order["taken"] == 0 ){ 
            $admins = admins($conn);
            $newBalance = intval(balance($conn , $order["user_id"])["available"] ?? 0) + (intval($value) / 100 )  ;
            update_balance($conn , $order["user_id"]  ,  "available_balance" , $newBalance );
            $conn->query("update ");
            
            $currentUser = is_user($conn ,  $order["user_id"]);
            if ($currentUser['check']){
                $name = $currentUser["user"]["first_name"];
            }else{
                $name = "غير معروف";
            }
            foreach ($admins["admins"] as $admin){
                     send_noti($conn , $admin , " قام " . $name  . "  باضافه رصيد قيمته  " .   strval($value / 100) . " ويكون الاجمالي "  . $newBalance  , "");

            }

            send_noti($conn , $order["user_id"] , "تم اضافه "  . strval($value / 100 ) . " الي حسابك بنجاح"  , "account");
           
           
            $id  = $order["id"];
            $conn->query("update payment_orders set taken = 1 where id = $id");
            $conn->query("update payment_orders set stata = 200  where id = $id");
        }else{
            header("Location: ./account");
            exit();
        }
        
    }elseif( ( !$success) &&  ($order["error"] == 0) ) {
        $conn->query("update payment_orders set stata = 300  where order_id = $order_id");
		 send_noti($conn , $order["user_id"] , "لم يتم اضافه الرصيد ربما توجد مشكله لديك او يمكنك ارسال شكوي"  , "support");
		
		
    }
}

if ( strtolower($_SERVER['REQUEST_METHOD'] )=== "get"){
    if ($status){
				
				
              header("Location: ./paymob-return?" . http_build_query(["error" => encrypt_decrypt( 0) , "message" =>   encrypt_decrypt( "تم اضافه الرصيد بنجاح" )   , 
       ]));
     exit();  
    }else{
		
		$messageTo =<<<"Msg"
			<div  > 
				<h5> لم يتم اضافه الرصيد ربما توجد مشكله فى الخدمات التاليه</h5>
				<ul class="text-left"><li class="lead mb-3">لا يوجد رصيد فى بطاقتك</li></ul>
				<ul class="text-left"><li class="lead mb-3"> بطاقه الدفع خطأ</li></ul>
				<ul class="text-left"><li class="lead mb-3"> توجد مشكله فى البنك المصدر</li></ul>
				<ul class="text-left"><li class="lead mb-3"> لا يوجد رصيد فى محفظتك</li></ul>
				<ul class="text-left"><li class="lead mb-3"> توجد مشكله فى المحفظه</li></ul>
			</div
Msg;
		
		
		
              header("Location: ./paymob-return?" . http_build_query(["error" => encrypt_decrypt(1) , "message" => encrypt_decrypt($messageTo)   , "button" => encrypt_decrypt(   "حاول مره اخري")
      ,
      "url" => encrypt_decrypt( "/pages/paymob") 
       ]));
       exit();
    }
    
}

//header("Location: ./index");
//exit();
