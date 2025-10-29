<?php
session_start();
include 'db.php';
require __DIR__ . '/vendor/autoload.php';

// 1. Get User ID and Registration ID
$user_id = $_SESSION['user_id'] ?? null;
$registration_id = $_GET['id'] ?? 0;

if (!$user_id) {
    die("Access Denied: You must be logged in to view this certificate.");
}

// 2. Securely fetch data
// Checks that the registration ID BOTH exists AND belongs to the logged-in user.
$sql = "SELECT 
            r.name AS reg_name, 
            r.registration_time AS reg_date,
            s.name AS event_name,
            s.date AS event_date,
            m.name AS main_event_name
        FROM registrations r
        JOIN sub_events s ON r.sub_event_id = s.id
        JOIN main_events m ON s.main_event_id = m.id
        WHERE r.id = ? AND r.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $registration_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Access Denied: No certificate found for your account.");
}

$data = $result->fetch_assoc();
$stmt->close();

// 3. --- Create the PDF using TCPDF ---
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Event Portal');
$pdf->SetTitle('Certificate of Registration');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Border
$pdf->Rect(10, 10, 190, 277, 'S'); 

// Title
$pdf->SetFont('helvetica', 'B', 32);
$pdf->Cell(0, 50, 'Certificate of Registration', 0, 1, 'C', 0, '', 0);

// "This certificate is presented to"
$pdf->SetFont('helvetica', '', 16);
$pdf->Cell(0, 30, 'This certificate is presented to', 0, 1, 'C', 0, '', 0);

// Attendee Name
$pdf->SetFont('helvetica', 'B', 28);
$pdf->Cell(0, 20, $data['reg_name'], 0, 1, 'C', 0, '', 0);

// "For successfully registering for"
$pdf->SetFont('helvetica', '', 16);
$pdf->Cell(0, 30, 'For successfully registering for the event', 0, 1, 'C', 0, '', 0);

// Event Name
$pdf->SetFont('helvetica', 'B', 22);
$pdf->Cell(0, 20, $data['event_name'], 0, 1, 'C', 0, '', 0);

// Parent Event Name
$pdf->SetFont('helvetica', 'I', 14);
$pdf->Cell(0, 15, '(part of the ' . $data['main_event_name'] . ')', 0, 1, 'C', 0, '', 0);

// Date of Event
$pdf->SetFont('helvetica', '', 14);
$event_date_formatted = date('F j, Y', strtotime($data['event_date']));
$pdf->Cell(0, 20, 'Event Date: ' . $event_date_formatted, 0, 1, 'C', 0, '', 0);

// Registration Date
$pdf->SetY(-40); // Position 40mm from bottom
$pdf->SetFont('helvetica', '', 10);
$reg_date_formatted = date('F j, Y', strtotime($data['reg_date']));
$pdf->Cell(0, 10, 'Issued on: ' . $reg_date_formatted, 0, 1, 'C', 0, '', 0);
$pdf->Cell(0, 10, 'Registration ID: #' . $registration_id, 0, 1, 'C', 0, '', 0);

// 4. Output the PDF (I = inline, D = download)
$pdf->Output('certificate_of_registration.pdf', 'I');
?>