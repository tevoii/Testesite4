<?php
  define("CASINOENGINE", true);
  session_start();
  include_once("../../../engine/config/config.php");
  include_once( "../../../modules/partner/partner.php");
  error_reporting(0);
  $module_robokassa_query = @mysql_fetch_array(@mysql_query("select * from pay_modules_robokassa"));
  $mrh_pass2              = $module_robokassa_query['mrh_pass2'];
  $tm                     = getdate(time() + 9 * 3600);
  $date                   = "{$tm['year']}-{$tm['mon']}-{$tm['mday']} {$tm['hours']}:{$tm['minutes']}:{$tm['seconds']}";
  $out_summ               = $_REQUEST['OutSum'];
  $inv_id                 = floatval($_REQUEST['InvId']);
  $shp_item               = $_REQUEST['Shp_item'];
  $crc                    = $_REQUEST['SignatureValue'];
  $shp_item               = $_REQUEST['Shp_item'];
  $login                  = $_REQUEST['login'];
  $crc                    = strtoupper($crc);
  $my_crc                 = strtoupper(md5("{$out_summ}:{$inv_id}:{$mrh_pass2}:Shp_item={$shp_item}"));
  if ($my_crc != $crc) {
      echo "�� ������ �������\n";
      exit();
  }
  echo "OK{$inv_id}\n";
  $referer = $_SERVER['REMOTE_ADDR'];
  @mysql_query("update pay_deposits set status = '1', referer = '" . $referer . "' where id ='" . $inv_id . "'");
  $pay_query = @mysql_fetch_array(@mysql_query("select * from pay_deposits where id ='" . $inv_id . "'"));
  payToReferer($pay_query['user'], $out_summ);
  @mysql_query("update clients set cash=cash+'" . $out_summ . "' where login='" . $pay_query['user'] . "'");
  @mysql_query("update clients set cashin=cashin+'" . $out_summ . "' where login='" . $pay_query['user'] . "'");
  $config_query  = @mysql_fetch_array(@mysql_query("select * from casino_settings"));
  $site          = $config_query['siteadress'];
  $email_support = $config_query['emailcasino'];
  $priority      = 3;
  $format        = "text/html";
  $msg           = "";
  $msg .= "������������, �������������,<br>";
  $msg .= "������������:" . $pay_query['user'] . "<br><br>";
  $msg .= "�������� ������� ���� ��: " . $pay_query['amount'] . " ��������<br>";
  $msg .= "---------------------<br>";
  $msg .= "� ���������� �����������,<br>";
  $msg .= "����� ��������-������ " . $site . "<br>";
  @mail($email_support, "���������� ����� �� �����: " . $pay_query['amount'] . " ��������", $msg, "From: {$email_support}\nContent-Type:{$format};charset=windows-1251\nMIME-Version: 1.0\nContent-Transfer-Encoding: 8bit\nX-Priority: {$priority}\nX-Mailer:CasinoEngine mail v1.0");
  $config_query = @mysql_fetch_array(@mysql_query("select * from casino_settings"));
  $site         = $config_query['siteadress'];
  $user_query   = @mysql_fetch_array(@mysql_query("select * from clients where login='" . $pay_query['user'] . "'"));
  $priority     = 3;
  $format       = "text/html";
  $msg          = "";
  $msg .= "������������, " . $user_query['login'] . ",<br>";
  $msg .= "<br>";
  $msg .= "�� �������� ������� ���� ��: " . $pay_query['amount'] . " ��������<br>";
  $msg .= "---------------------<br>";
  $msg .= "� ���������� �����������,<br>";
  $msg .= "����� ��������-������ " . $site . "<br>";
  @mail($user_query['email'], "���������� ����� �� �����: " . $pay_query['amount'] . " ��������", $msg, "From: {$email_support}\nContent-Type:{$format};charset=windows-1251\nMIME-Version: 1.0\nContent-Transfer-Encoding: 8bit\nX-Priority: {$priority}\nX-Mailer:CasinoEngine mail v1.0");
  echo "\r\n\r\n";
?>