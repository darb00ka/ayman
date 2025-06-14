<?php 
/**
 */

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
function getIntegrateId( $type ){
    global $conn ;
    $back = ["error" => 1 ]; 
    if ( ($result =  $conn->query("select * from payment_integrations where type = '" . safe($type , "string") . "'"))  && (mysqli_num_rows($result) > 0)){
            $result = $result->fetch_assoc();
            $back["error"] = 0 ; 
            $back["int_id"] = $result["int_id"]; 
    }
    return $back ; 
}

function createOrder($order_id , $trans_id , $user_id , $type , $value  ){
    global $conn ; 
    $back = ["error" => 1 ] ;
    $order_id = safe($order_id , "int");
    $value = floatval($value) ;
    $type = safe($type , "string");
    $trans_id = safe($trans_id , "int");
    $user_id  = safe($user_id , "int");
    if($conn->query("insert into payment_orders values (NULL , $order_id , $trans_id , $user_id  , $value , 400 , 0 ,  '$type' )")){
       $back["error"] = 0 ; 
    }
    return $back ;
}

session_start();
include '../inc/func.php';
remember();
if (!$_SESSION['id']) {
    header("Location: ../login");
    exit();
    
}
$paymentType = $_POST["type"] ?? "" ;
$int_id  = getIntegrateId($_POST["type"] );
$value = safe($_POST['value'] ?? 1  , "int");
$phone = safe($_POST['phone'] ?? "" , "string" );
if( ( !isset($int_id['int_id']) ) ||  (intval($value) < 1  ) ){

      header("Location: ./paymob-return.php?" . http_build_query(["error" =>  encrypt_decrypt(1) , "message" =>  encrypt_decrypt( "يبدو ان المبلغ الذى تم ادخاله اقل من الحد المطلوب" ),  "button" => 			encrypt_decrypt( "حاول مره اخري") ,
      "url" => encrypt_decrypt("/pages/paymob")
       ]));


       exit();
}
$value = strval($value) . "00";
$int_id = $int_id["int_id"];
boolval($phone) ? '' : $phone = $_SESSION['phone'] ; 




/*
array(10) {
  ["id"]=>
  int(1)
  ["first_name"]=>
  string(16) "واسطيتكو"
  ["last_name"]=>
  string(0) ""
  ["email"]=>
  string(18) "wastetco@gmail.com"
  ["phone"]=>
  string(11) "01026252444"
  ["nid"]=>
  string(14) "29504012507876"
  ["account_status"]=>
  string(6) "Active"
  ["acc_type"]=>
  string(5) "admin"
  ["verified"]=>
  string(3) "yes"
  ["admin"]=>
  int(1)
}


*/

// phone
use Alaa\Paymob\Exceptions\PaymentException;
use Alaa\Paymob\Facade\Paymob;
require_once './paymob-sdk/vendor/autoload.php';
$accountInfo = [
    "api_key" => "ZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKSVV6VXhNaUo5LmV5SndjbTltYVd4bFgzQnJJam94TWpjME5UTXNJbU5zWVhOeklqb2lUV1Z5WTJoaGJuUWlMQ0p1WVcxbElqb2lNVFl6TkRnek9EY3hNaTQ1TnpBNE5URWlmUS4xRFNfMEM1cUdGMThDcTVCNEt1cTk4b1R0NFNsaTlSdUtrVTZJM3FTT19hYk1jNHE1UnJhWWtDVUFVcUtFMy1TRWdSS2J5Sm5WNHhJejNHOVhTeHprZw==",
    "HMAC" => "DCD461D9A8E0FB92BCF73ABB171C6D8F",
    "iframe" => "289179",

];


try {

    Paymob::prepare($accountInfo, [
        "delivery_needed" => false,
        "amount_cents" => $value,
        "currency" => "EGP",
        "items" => []
    ]);
    $paymentInfo = [
        "amount_cents" => $value,
        "currency" => "EGP",
        "billing_data" => [
            "apartment" => "NA" ,
            "email" => $_SESSION["email"],
            "floor" => "NA" ,
            "first_name" => $_SESSION['first_name'],
            "street" => "NA" ,
            "building" => "NA" ,
            "phone_number" => $_SESSION['phone'] ,
            "shipping_method" => "NA" ,
            "postal_code" => "NA" ,
            "city" => "NA" ,
            "country" => "NA" ,
            "last_name" => strlen($_SESSION['last_name']) >  0  ? $_SESSION['last_name'] : "غير معروف",
            "state" => "NA" ,
        ],
        "integration_id" => intval($int_id)
    ];
    $paymentReq = '' ;
    if( 'online_card' === strtolower($paymentType)){
       $paymentReq = Paymob::payWithOnlineCard($paymentInfo);  
        if(createOrder($paymentReq["order_id"] , $paymentReq["integration_id"] , $_SESSION['id'] ,  strtolower($paymentType) , $value)["error"] == 0 ){
            header("Location: " . $paymentReq["redirect_url"]);
            exit();
        }
        
    }elseif( 'kiosk' === strtolower($paymentType)){
         $paymentReq = Paymob::payWithKiosk($paymentInfo);
         
         
              if(createOrder($paymentReq["order"]["id"] , $paymentReq["id"] , $_SESSION['id'] ,  strtolower($paymentType) , $value)["error"] == 0 ){
    			$bill = $paymentReq["data"]["bill_reference"];
    			$message = "الرقم المرجعى للمدفوعات :" . "<span class='text-success'>" . $bill .  "</span><br>"  . "طريقة الدفع: رجاء التوجه إلى أقرب فرع أمان أو مصاري أو ممكن أو سداد و أسأل عن 'مدفوعات اكسبت' و أخبرهم بالرقم المرجعي" ;
                      header("Location: ./paymob-return.php?" . http_build_query(["error" => encrypt_decrypt(0) , "message" =>   encrypt_decrypt($message)  ]));
                      exit();
              }
        
    }elseif( 'mobile_wallet' === strtolower($paymentType)){
         $paymentReq = Paymob::payWithMobileWallet($paymentInfo , $phone); 
        if(createOrder($paymentReq["order"]["id"] , $paymentReq["id"] , $_SESSION['id'] ,  strtolower($paymentType) , $value)["error"] == 0 ){
            $url = strlen($paymentReq["redirect_url"]) > 0 ? $paymentReq["redirect_url"] : $paymentReq["iframe_redirection_url"] ;
            header("Location: " . $url );
            exit();
        }
    }
    
      header("Location: ./paymob-return.php?" . http_build_query(["error" =>  encrypt_decrypt(1)   , "button" =>  encrypt_decrypt( "حاول مره اخري"  )
      ,
      "url" => encrypt_decrypt( "/pages/paymob")
      
       ]));
       exit();
} catch (PaymentException $e) {

      header("Location: ./paymob-return.php?" . http_build_query(["error" => encrypt_decrypt(1) 
      , "message" => encrypt_decrypt( "خطأ فني حاول مره اخري"  )  , "button" => encrypt_decrypt (  "حاول مره اخري"
       ),
      "url" =>  encrypt_decrypt("/pages/paymob")
      
       ]));
       exit();
}

/*
    get url or bill refrannce to make payment 

 * 1)  select payment type and get intrgral id 
 * 2) make request to paymob server
   3) insert request to db and set state to 400 (wating)
   4) send url or bill to user 
   
 * 
 * 
 
 echo "<pre>";
 var_dump($_POST);
echo "</pre>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

 * callback page  
    1) get order details from db 
    2) update order state 
    3) send notication to admin 
    3) update user balance 
 */
 
 /**
  * cheker page 
  * 1) every 20 secound send request to get state if equal 200 return ok else if  300 return faild  else  return wating
  * 
  */
