<?php

class Mpdf extends Su_Controller
{
    private $mpdfTemplatePath =  'su/mpdf/';

    public function index()
    {
        $filename = UploadModel::$rootFolder . DIRECTORY_SEPARATOR . "test.pdf";

        $data["title"] = __METHOD__;
        $pdf = new \Mpdf\Mpdf();
        $pdf->SetTitle($data["title"]);
        $pdf->SetHeader($data["title"] . '|{PAGENO}|' . date('d-m-Y H:i:s'));
        $pdf->SetFooter(c_key('webapp_title') . '|{PAGENO}|' . date('d-m-Y H:i:s'));

        $dataHeader['hostname'] = site_url();
        $pdf->WriteHTML($this->load->view($this->mpdfTemplatePath . 'first', $dataHeader, true));
        $pdf->AddPage();

        $dataBody['pic'] = sys_asset_url('img/0.png');
        $pdf->Bookmark("HTTP Header", 0);
        $pdf->SetWatermarkText("HTTP Header");
        $pdf->showWatermarkText = true;
        $pdf->WriteHTML($this->load->view($this->mpdfTemplatePath . 'body', $dataBody, true));
        $pdf->AddPage();

        $pdf->Bookmark("Plugin", 0);
        $dataFooter = [];
        $pdf->WriteHTML($this->load->view($this->mpdfTemplatePath . 'last', $dataFooter, true));

        $pdf->SetWatermarkText("Plugin");
        $pdf->showWatermarkText = true;
        $pdf->watermark_font = 'DejaVuSansCondensed';
        $pdf->watermarkTextAlpha = 0.1;
        $pdf->Output();
    }

    public function invoice()
    {
        $data["title"] = __METHOD__;
        $html = $this->load->view($this->mpdfTemplatePath . __FUNCTION__, $data, true);
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 48,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);

        $mpdf->SetProtection(array('print'));
        $mpdf->SetTitle("Acme Trading Co. - Invoice");
        $mpdf->SetAuthor("Acme Trading Co.");
        $mpdf->SetWatermarkText("Paid");
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);

        $mpdf->Output();
    }

    public function onepage()
    {
        $data["title"] = __METHOD__;
        $data["logo"] = sys_asset_url('img/0.png');
        $html = $this->load->view($this->mpdfTemplatePath . __FUNCTION__, $data, true);
        $pdf = new \Mpdf\Mpdf();
        $pdf->SetTitle($data["title"]);
        $pdf->SetHeader(c_key('webapp_title') . '|{PAGENO}|' . date('d-m-Y H:i:s'));
        $pdf->SetFooter(c_key('webapp_title') . '|{PAGENO}|' . date('d-m-Y H:i:s'));
        $pdf->WriteHTML($html);
        $base64 = base64_encode($pdf->Output(" ", 'S'));
        $file_content = base64_decode($base64);
        $filename = __FUNCTION__ . '.pdf';
        force_download($filename, $file_content);

        $tmp_name = UploadModel::$rootFolder . DIRECTORY_SEPARATOR . $filename;
        $pdf->Output($tmp_name, 'F');
        exit(base64_encode(file_get_contents($tmp_name)));
    }
}
