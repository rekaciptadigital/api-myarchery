<?php

namespace App\BLoC\App\Certificate;

use App\Models\ArcheryEventCertificateTemplates;
use App\Models\ArcheryMemberCertificate;
use DAI\Utils\Abstracts\Retrieval;
use DAI\Utils\Helpers\BLoC;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\ArcheryEventParticipant;
use DAI\Utils\Exceptions\BLoCException;
use Mpdf\Output\Destination;
use Barryvdh\DomPDF\Facade as PDF;
use App;

class GetDownload extends Retrieval
{
  public function getDescription()
  {
    return "";
  }

  protected function process($parameters)
  {
    $participant_id = $parameters->get('participant_id');
    $user = Auth::guard('app-api')->user();
    $type_certificate = $parameters->get('type_certificate');

    $member = ArcheryEventParticipant::getMemberByUserId($user['id'], $participant_id);
    if (!$member) throw new BLoCException("anda tidak mengikuti event ini");

    $member_id = $member->id;
    $event_id = $member->event_id;
    $member_name = $member->name;

    $certificate = ArcheryEventCertificateTemplates::getCertificateByEventAndType($event_id, $type_certificate);
    if (!$certificate)
      throw new BLoCException("event dan/atau tipe sertifikat tidak ditemukan");

    $html_template = base64_decode($certificate->html_template);

    $category = ArcheryEventCertificateTemplates::getCategoryLabel($participant_id, $user['id']);
    if ($category == "")
      throw new BLoCException("kategori tidak ditemukan");

    $category_name = $category;

    $list = ArcheryEventCertificateTemplates::getTypeCertificate();

    $final_doc = str_replace(['{%member_name%}', '{%category_name%}'], [$member_name, $category_name], $html_template);

    if ($type_certificate == $list['winner']) {
      $get_peringkat = ArcheryEventCertificateTemplates::checkElimination($member_id);
      if (!$get_peringkat || $get_peringkat->elimination_ranked > 4)
        throw new BLoCException("data elimination tidak ditemukan");
      $ranked = $get_peringkat->elimination_ranked;

      $final_doc = str_replace(['{%ranked%}'], [$ranked], $final_doc);
    }

    $member_certificate_id = $member_id . "-" . $certificate->id;
    $validate_link = env("WEB_URL") . "/certificate/validate/" . $member_certificate_id;
    $final_doc = str_replace(['{%certificate_verify_url%}'], [$validate_link], $final_doc);

    $file_name = str_replace(" ", "-", $member_name) . "_certificate_" . ArcheryEventCertificateTemplates::getCertificateLabel($type_certificate) . ".pdf";
    $mpdf = new \Mpdf\Mpdf([
      'margin_left' => 0,
      'margin_right' => 0,
      'mode' => 'utf-8',
      'format' => 'A4-L',
      'orientation' => 'L',
      'bleedMargin' => 0,
      'dpi'        => 110,
      'tempDir' => public_path() . '/tmp/pdf'
    ]);

    $member_certificate = ArcheryMemberCertificate::firstOrNew(array(
      'id' => $member_certificate_id,
      'member_id' => $member_id,
      'certificate_template_id' => $certificate->id,
    ));
    $member_certificate->save();

    if (env("APP_ENV") != "production")
      $mpdf->SetWatermarkText('EXAMPLE');

    $mpdf->SetDisplayPreferences('FullScreen');
    $mpdf->WriteHTML($final_doc);
    $pdf = $mpdf->Output($file_name, Destination::STRING_RETURN);

    $b64_pdf = "data:application/pdf;base64," . base64_encode($pdf);

    return [
      "file_name" => $file_name,
      "file_base_64" => $b64_pdf,
    ];
  }
}
