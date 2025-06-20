<?php
// PDF Export Library using TCPDF (if available) or simple HTML to PDF
class ReportPDFExporter
{

    public static function exportToPDF($data, $report_type, $date_from, $date_to)
    {
        // Try to use TCPDF if available, otherwise fallback to simple HTML
        if (class_exists('TCPDF')) {
            self::exportWithTCPDF($data, $report_type, $date_from, $date_to);
        } else {
            self::exportWithHTML($data, $report_type, $date_from, $date_to);
        }
    }

    private static function exportWithHTML($data, $report_type, $date_from, $date_to)
    {
        $filename = "report_" . $report_type . "_" . date('Y-m-d') . ".pdf";

        // Set headers for PDF download
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . $filename . '"');

        echo self::generateHTMLContent($data, $report_type, $date_from, $date_to);

        // Add JavaScript to trigger print dialog
        echo "<script>
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        </script>";
    }

    private static function exportWithTCPDF($data, $report_type, $date_from, $date_to)
    {
        // TCPDF implementation
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Order Management System');
        $pdf->SetAuthor('System Administrator');
        $pdf->SetTitle('Laporan Order - ' . ucfirst($report_type));
        $pdf->SetSubject('Report Export');

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Generate HTML content
        $html = self::generateHTMLContent($data, $report_type, $date_from, $date_to, true);

        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $filename = "report_" . $report_type . "_" . date('Y-m-d') . ".pdf";
        $pdf->Output($filename, 'D');
    }

    private static function generateHTMLContent($data, $report_type, $date_from, $date_to, $for_pdf = false)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Laporan Order</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: ' . ($for_pdf ? '10px' : '20px') . ';
                    font-size: ' . ($for_pdf ? '11px' : '12px') . ';
                    line-height: 1.4;
                }
                h1, h2, h3 {
                    color: #333;
                    margin-bottom: 10px;
                }
                h1 { font-size: ' . ($for_pdf ? '18px' : '24px') . '; }
                h2 { font-size: ' . ($for_pdf ? '16px' : '20px') . '; }
                h3 { font-size: ' . ($for_pdf ? '14px' : '16px') . '; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: ' . ($for_pdf ? '5px' : '8px') . ';
                    text-align: left;
                    vertical-align: top;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    font-size: ' . ($for_pdf ? '10px' : '11px') . ';
                }
                td {
                    font-size: ' . ($for_pdf ? '9px' : '10px') . ';
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .summary-box {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                    border: 1px solid #dee2e6;
                }
                .header-info {
                    margin-bottom: 20px;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: ' . ($for_pdf ? '8px' : '10px') . ';
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>';

        $html .= '<div class="header-info">
            <h1>Laporan ' . ($report_type === 'summary' ? 'Ringkasan' : 'Detail') . ' Order</h1>
            <p><strong>Periode:</strong> ' . date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)) . '</p>
            <p><strong>Tanggal Generate:</strong> ' . date('d/m/Y H:i:s') . '</p>
        </div>';

        if ($report_type === 'summary') {
            $html .= self::generateSummaryHTML($data, $for_pdf);
        } else {
            $html .= self::generateDetailedHTML($data, $for_pdf);
        }

        $html .= '<div class="footer">
            Laporan digenerate oleh Sistem Pencatatan Order pada ' . date('d/m/Y H:i:s') . '
        </div>';

        $html .= '</body></html>';

        return $html;
    }

    private static function generateSummaryHTML($data, $for_pdf = false)
    {
        $html = '<div class="summary-box">
            <h3>Ringkasan Statistik</h3>
            <table>
                <tr><td><strong>Total Order</strong></td><td class="text-right">' . number_format($data['summary']['total_orders']) . '</td></tr>
                <tr><td><strong>Total Revenue</strong></td><td class="text-right">Rp ' . number_format($data['summary']['total_revenue'], 0, ',', '.') . '</td></tr>
                <tr><td><strong>Rata-rata Nilai Order</strong></td><td class="text-right">Rp ' . number_format($data['summary']['avg_order_value'], 0, ',', '.') . '</td></tr>
                <tr><td><strong>Order Selesai</strong></td><td class="text-right">' . number_format($data['summary']['completed_orders']) . '</td></tr>
                <tr><td><strong>Order Pending</strong></td><td class="text-right">' . number_format($data['summary']['pending_orders']) . '</td></tr>
                <tr><td><strong>Order Dibatalkan</strong></td><td class="text-right">' . number_format($data['summary']['cancelled_orders']) . '</td></tr>
            </table>
        </div>';

        $html .= '<h3>Breakdown Harian</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="text-center">Total Order</th>
                    <th class="text-right">Revenue Harian</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['daily'] as $day) {
            $html .= '<tr>
                <td>' . date('d/m/Y', strtotime($day['order_date'])) . '</td>
                <td class="text-center">' . number_format($day['total_orders']) . '</td>
                <td class="text-right">Rp ' . number_format($day['daily_revenue'], 0, ',', '.') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private static function generateDetailedHTML($data, $for_pdf = false)
    {
        $html = '<table>
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th class="text-right">Total Amount</th>
                    <th class="text-center">Status</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>';

        $total_amount = 0;
        foreach ($data as $order) {
            $total_amount += $order['total_amount'];
            $html .= '<tr>
                <td>' . htmlspecialchars($order['order_number']) . '</td>
                <td>' . date('d/m/Y', strtotime($order['order_date'])) . '</td>
                <td>' . htmlspecialchars($order['customer_name'] ?? '-') . '</td>
                <td>' . htmlspecialchars($order['customer_email'] ?? '-') . '</td>
                <td class="text-right">Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</td>
                <td class="text-center">' . ucfirst(htmlspecialchars($order['status'])) . '</td>
                <td>' . htmlspecialchars($order['payment_method'] ?? '-') . '</td>
            </tr>';
        }

        $html .= '<tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="4" class="text-right">Total:</td>
            <td class="text-right">Rp ' . number_format($total_amount, 0, ',', '.') . '</td>
            <td colspan="2"></td>
        </tr>';

        $html .= '</tbody></table>';

        return $html;
    }
}
?>