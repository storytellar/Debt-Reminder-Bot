<?php 
// ========================================================================================
// ===== Đây là bot nhắc nợ dựa trên dữ liệu GoogleSheet và Bot Telegram (Trợ lý của Thái)
// ===== Sử dụng hàm getGoogleSheets() , sendMessage()
// ===== Hướng dẫn: thay $spreadsheet_url, $token, $user_id, sau đó chạy cronjob 24h/lần
// ========================================================================================

// Khai báo
$spreadsheet_url="https://docs.google.com/spreadsheets/d/e/2<...>...=0&single=true&output=csv";
$token = "..."; // Token Bot
$user_id = [00000000]; // array of Telegram user

// Lấy dữ liệu từ Google Sheets
$spreadsheet_data = getGoogleSheets($spreadsheet_url);
$numberOfRows =  count($spreadsheet_data);

// Đọc dữ liệu và thông báo những ai đến hạn
for ($i=2; $i < $numberOfRows; $i++) {
        // ===============DỊCH DỮ LIỆU===================
        $data_id        = $spreadsheet_data[$i][0];
        $data_name      = $spreadsheet_data[$i][1];
        $data_money     = $spreadsheet_data[$i][2];
        $data_interest  = $spreadsheet_data[$i][3];
        $data_dateStart = $spreadsheet_data[$i][4];
        $data_dateEnd   = $spreadsheet_data[$i][5];
        $data_note      = $spreadsheet_data[$i][6];

        $distanceFromToday = compareDateWithToday(convertDateFormat($data_dateEnd));
        $distanceFromStart = compareDateWithToday(convertDateFormat($data_dateStart));
        
        if ($distanceFromToday==0 && $data_note != "gạch") {
            // Nếu hôm nay là cuối hạn
            sendMessage(    $token, $user_id,
                            "- Nhắc nợ cho sếp's !!!" . "\n" .
                            "⚠️ HÔM NAY LÀ HẠN CHÓT ⚠️" . "\n" .
                            "- Người nợ là 👨🏻‍🦰 " . $data_name . " (" . $data_id . ")" . "\n" .
                            "- Số tiền: " . $data_money . " triệu\n" .
                            "- Thời gian: " . $data_dateStart . " - " . $data_dateEnd . "\n" .
                            "- Lãi: " . $data_interest . "% / ngày" . "\n\n" .
                            "- Ghi chú: " . $data_note
                        );
        }
        else if ($data_note != "gạch"){
            // Check những ai còn nợ nhưng chưa đến cuối hạn
            $remindDate = array(31,61,91,121,151,
                                40,70,100,130,160,
                                50,80,110,140,170);
            $isTodayInReminder = in_array(abs($distanceFromStart), $remindDate);
            if ($isTodayInReminder) {
                // Nếu hôm nay là ngày thu lãi
                // Công thức tính tiền chẵn 10 ngày thu 1 lần: (Lãi/100) * (Số tiền)*(1000k) * (10 ngày)
                $moneyMustPay = str_replace(',', '.', $data_interest) / 100 * $data_money*1000 * 10;
                sendMessage(    $token, $user_id,
                                "- Nhắc nợ cho sếp's !!!" . "\n" . 
                                "⏰ HÔM NAY LÀ NGÀY THỨ " . abs($distanceFromStart%30) . " CỦA THÁNG THỨ " . (abs($distanceFromStart%30 - $distanceFromStart)/30+1) . "\n" .
                                "- Người nợ là 👨🏻‍🦰 " . $data_name . " (" . $data_id . ")" . "\n" .
                                "- Số tiền: " . $data_money . " triệu\n" .
                                "- Thời gian: " . $data_dateStart . " - " . $data_dateEnd . "\n" .
                                "- Lãi: " . $data_interest . "% / ngày" . "\n" .
                                "=> SỐ TIỀN LÃI CẦN THU: " . $moneyMustPay . "k" . "\n\n" .
                                "- Ghi chú: " . $data_note
                            );
            }
        }
}


function getGoogleSheets($spreadsheet_url_CSV){
    if(!ini_set('default_socket_timeout', 15)) echo "<!-- unable to change socket timeout -->";
    if (($handle = fopen($spreadsheet_url_CSV, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spreadsheet_data[] = $data;
        }
        fclose($handle);
    }
    else
        die("Problem reading csv");
    return $spreadsheet_data;
}

function sendMessage($token,$user_id,$message){
    foreach ($user_id as $key => $value) {
        $request_params = [
            'chat_id' => $value,
            'text' => $message
        ];
        $request_url = 'https://api.telegram.org/bot' . $token . '/sendMessage?' . http_build_query($request_params);
        file_get_contents($request_url);
    }
}

function convertDateFormat($date){
    $date = str_replace('/', '-', $date);
    return date('Y-m-d', strtotime($date));
}
  
function compareDateWithToday($date) {
    $date = convertDateFormat($date);
    $date = date_create($date);
    $today = date('Y-m-d');
    $today = date_create($today);
    $distance = date_diff($today,$date)->format("%R%a");
    return $distance;
}
?>