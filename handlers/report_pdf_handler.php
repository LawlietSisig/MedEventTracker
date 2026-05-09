<?php
/**
 * Report PDF Export Handler
 * Medical Outreach Tracker — Dompdf-based PDF generation
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

requireLogin();

$type    = $_GET['type'] ?? '';
$allowed = ['events', 'patients', 'volunteers', 'summary'];
if (!in_array($type, $allowed)) { http_response_code(400); die('Invalid report type.'); }

$conn      = getConnection();
$generated = date('F j, Y \a\t g:i A');
$logo      = '📋';

// ── Shared CSS ────────────────────────────────────────────────────────────────
$css = '
<style>
  @page { margin: 18mm 16mm 18mm 16mm; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e2b25; background: #fff; }

  .header { border-bottom: 3px solid #1b7a4d; padding-bottom: 10px; margin-bottom: 18px; }
  .header-inner { display: table; width: 100%; }
  .header-left  { display: table-cell; vertical-align: middle; }
  .header-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 7.5pt; color: #607870; }
  .brand-name   { font-size: 15pt; font-weight: bold; color: #1b7a4d; letter-spacing: -0.5px; }
  .brand-sub    { font-size: 8pt; color: #607870; margin-top: 1px; }
  .report-title { font-size: 13pt; font-weight: bold; color: #0f4a2d; margin: 14px 0 4px; }
  .report-meta  { font-size: 7.5pt; color: #607870; margin-bottom: 14px; }

  /* Stat row */
  .stat-row { display: table; width: 100%; margin-bottom: 18px; border-collapse: separate; border-spacing: 8px; }
  .stat-box { display: table-cell; background: #f0fdf4; border: 1.5px solid #a5d4bb; border-radius: 6px; padding: 10px 14px; text-align: center; width: 33%; }
  .stat-box .val { font-size: 20pt; font-weight: bold; color: #1b7a4d; line-height: 1; }
  .stat-box .lbl { font-size: 7pt; color: #607870; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 3px; }

  /* Section heading */
  .section-head { background: #1b7a4d; color: #fff; font-size: 8.5pt; font-weight: bold;
                  padding: 5px 10px; border-radius: 4px 4px 0 0; text-transform: uppercase;
                  letter-spacing: 0.05em; margin-top: 16px; }

  /* Table */
  table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  thead th { background: #eef7f2; color: #0f4a2d; font-size: 7.5pt; font-weight: bold;
             padding: 6px 8px; text-align: left; border: 1px solid #a5d4bb; }
  tbody td { padding: 5px 8px; font-size: 8pt; border: 1px solid #dde5e0; vertical-align: top; }
  tbody tr:nth-child(even) td { background: #f8fafc; }

  /* Status badges */
  .badge { padding: 2px 7px; border-radius: 10px; font-size: 7pt; font-weight: bold; }
  .badge-upcoming  { background: #dbeafe; color: #1d4ed8; }
  .badge-ongoing   { background: #d1fae5; color: #065f46; }
  .badge-completed { background: #f0fdf4; color: #14532d; border: 1px solid #a5d4bb; }
  .badge-cancelled { background: #fff1f2; color: #9f1239; }
  .badge-Male      { background: #dbeafe; color: #1d4ed8; }
  .badge-Female    { background: #fdf4ff; color: #7e22ce; }
  .badge-Other     { background: #f1f5f9; color: #475569; }

  /* Summary breakdown table */
  .breakdown-row td:last-child { font-weight: bold; color: #1b7a4d; text-align: right; }
  .pct-bar-wrap { background: #e8eeeb; border-radius: 3px; height: 7px; margin-top: 2px; }
  .pct-bar      { background: #1b7a4d; border-radius: 3px; height: 7px; }

  .footer { border-top: 1px solid #dde5e0; padding-top: 7px; margin-top: 20px;
            font-size: 7pt; color: #8fa99a; text-align: center; }
  .page-break { page-break-after: always; }
  .no-data { color: #8fa99a; font-style: italic; padding: 10px 8px; font-size: 8pt; }
</style>';

$footer = '<div class="footer">Medical Outreach Tracker &mdash; Confidential &mdash; Generated ' . $generated . '</div>';

// ── Helper: render header ─────────────────────────────────────────────────────
function pdfHeader(string $title, string $generated): string {
    return '
    <div class="header">
      <div class="header-inner">
        <div class="header-left">
          <div class="brand-name">&#x1F4CB; MedOutreach</div>
          <div class="brand-sub">Medical Outreach Tracker</div>
        </div>
        <div class="header-right">Generated<br>' . $generated . '</div>
      </div>
    </div>
    <div class="report-title">' . $title . '</div>
    <div class="report-meta">This report is generated automatically from live system data.</div>';
}

// ── Build HTML content ────────────────────────────────────────────────────────
ob_start();

if ($type === 'events') {
    // ── Events ────────────────────────────────────────────────────────────────
    $total = (int) $conn->query("SELECT COUNT(*) FROM outreach_events")->fetch_row()[0];
    $stats = [];
    $r = $conn->query("SELECT status, COUNT(*) c FROM outreach_events GROUP BY status");
    while ($row = $r->fetch_assoc()) $stats[$row['status']] = $row['c'];

    echo pdfHeader('Outreach Events Report', $generated);
    ?>
    <div class="stat-row">
      <div class="stat-box"><div class="val"><?= $total ?></div><div class="lbl">Total Events</div></div>
      <div class="stat-box"><div class="val"><?= ($stats['upcoming'] ?? 0) + ($stats['ongoing'] ?? 0) ?></div><div class="lbl">Active</div></div>
      <div class="stat-box"><div class="val"><?= $stats['completed'] ?? 0 ?></div><div class="lbl">Completed</div></div>
    </div>
    <div class="section-head">All Events</div>
    <table>
      <thead><tr>
        <th>#</th><th>Title</th><th>Location</th><th>Date</th><th>Time</th><th>Status</th><th>Max Vol.</th>
      </tr></thead>
      <tbody>
      <?php
      $res = $conn->query("SELECT id,title,location,event_date,end_event_date,start_time,end_time,status,max_volunteers FROM outreach_events ORDER BY event_date DESC");
      if ($res->num_rows === 0): ?>
        <tr><td colspan="7" class="no-data">No events found.</td></tr>
      <?php else: while ($row = $res->fetch_assoc()):
          $dateStr = date('M j, Y', strtotime($row['event_date']));
          if ($row['end_event_date'] && $row['end_event_date'] !== $row['event_date'])
              $dateStr .= ' – ' . date('M j, Y', strtotime($row['end_event_date']));
          $timeStr = date('g:i A', strtotime($row['start_time'])) . ' – ' . date('g:i A', strtotime($row['end_time']));
      ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td><?= $dateStr ?></td>
          <td><?= $timeStr ?></td>
          <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td><?= $row['max_volunteers'] ?? '—' ?></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
    <?php

} elseif ($type === 'patients') {
    // ── Patients ──────────────────────────────────────────────────────────────
    $total = (int) $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
    $male  = (int) $conn->query("SELECT COUNT(*) FROM patients WHERE gender='Male'")->fetch_row()[0];
    $female= (int) $conn->query("SELECT COUNT(*) FROM patients WHERE gender='Female'")->fetch_row()[0];

    echo pdfHeader('Patient Records Report', $generated);
    ?>
    <div class="stat-row">
      <div class="stat-box"><div class="val"><?= $total ?></div><div class="lbl">Total Patients</div></div>
      <div class="stat-box"><div class="val"><?= $male ?></div><div class="lbl">Male</div></div>
      <div class="stat-box"><div class="val"><?= $female ?></div><div class="lbl">Female</div></div>
    </div>
    <div class="section-head">Patient List</div>
    <table>
      <thead><tr>
        <th>#</th><th>Name</th><th>DOB</th><th>Age</th><th>Gender</th><th>Blood</th><th>Contact</th><th>Registered</th>
      </tr></thead>
      <tbody>
      <?php
      $res = $conn->query("SELECT id,first_name,last_name,dob,TIMESTAMPDIFF(YEAR,dob,CURDATE()) age,gender,blood_type,contact_number,created_at FROM patients ORDER BY created_at DESC");
      if ($res->num_rows === 0): ?>
        <tr><td colspan="8" class="no-data">No patients found.</td></tr>
      <?php else: while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
          <td><?= date('M j, Y', strtotime($row['dob'])) ?></td>
          <td><?= $row['age'] ?></td>
          <td><span class="badge badge-<?= $row['gender'] ?>"><?= $row['gender'] ?></span></td>
          <td><?= htmlspecialchars($row['blood_type'] ?? '—') ?></td>
          <td><?= htmlspecialchars($row['contact_number'] ?? '—') ?></td>
          <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
    <?php

} elseif ($type === 'volunteers') {
    // ── Volunteers ────────────────────────────────────────────────────────────
    $total = (int) $conn->query("SELECT COUNT(*) FROM volunteers")->fetch_row()[0];
    $profs = (int) $conn->query("SELECT COUNT(DISTINCT profession) FROM volunteers WHERE profession IS NOT NULL AND profession != ''")->fetch_row()[0];

    echo pdfHeader('Volunteer Roster Report', $generated);
    ?>
    <div class="stat-row">
      <div class="stat-box"><div class="val"><?= $total ?></div><div class="lbl">Total Volunteers</div></div>
      <div class="stat-box"><div class="val"><?= $profs ?></div><div class="lbl">Professions</div></div>
      <div class="stat-box"><div class="val"><?= date('Y') ?></div><div class="lbl">Report Year</div></div>
    </div>
    <div class="section-head">Volunteer List</div>
    <table>
      <thead><tr>
        <th>#</th><th>Name</th><th>Email</th><th>Contact</th><th>Profession</th><th>Registered</th>
      </tr></thead>
      <tbody>
      <?php
      $res = $conn->query("SELECT id,first_name,last_name,email,contact_number,profession,created_at FROM volunteers ORDER BY created_at DESC");
      if ($res->num_rows === 0): ?>
        <tr><td colspan="6" class="no-data">No volunteers found.</td></tr>
      <?php else: while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
          <td><?= htmlspecialchars($row['email'] ?? '—') ?></td>
          <td><?= htmlspecialchars($row['contact_number'] ?? '—') ?></td>
          <td><?= htmlspecialchars($row['profession'] ?? '—') ?></td>
          <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
    <?php

} elseif ($type === 'summary') {
    // ── Summary ───────────────────────────────────────────────────────────────
    $totalEvents    = (int) $conn->query("SELECT COUNT(*) FROM outreach_events")->fetch_row()[0];
    $totalPatients  = (int) $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
    $totalVolunteers= (int) $conn->query("SELECT COUNT(*) FROM volunteers")->fetch_row()[0];

    echo pdfHeader('Full Summary Report', $generated);
    ?>
    <div class="stat-row">
      <div class="stat-box"><div class="val"><?= $totalEvents ?></div><div class="lbl">Total Events</div></div>
      <div class="stat-box"><div class="val"><?= $totalPatients ?></div><div class="lbl">Total Patients</div></div>
      <div class="stat-box"><div class="val"><?= $totalVolunteers ?></div><div class="lbl">Total Volunteers</div></div>
    </div>

    <!-- Event Status -->
    <div class="section-head">Event Status Breakdown</div>
    <table>
      <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
      <tbody class="breakdown-row">
      <?php
      $r = $conn->query("SELECT status, COUNT(*) c FROM outreach_events GROUP BY status ORDER BY c DESC");
      while ($row = $r->fetch_assoc()):
          $pct = $totalEvents > 0 ? round($row['c'] / $totalEvents * 100, 1) : 0;
      ?>
        <tr>
          <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td><?= $row['c'] ?></td>
          <td>
            <?= $pct ?>%
            <div class="pct-bar-wrap"><div class="pct-bar" style="width:<?= $pct ?>%;"></div></div>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Patient Gender -->
    <div class="section-head">Patient Gender Distribution</div>
    <table>
      <thead><tr><th>Gender</th><th>Count</th><th>Share</th></tr></thead>
      <tbody class="breakdown-row">
      <?php
      $r = $conn->query("SELECT gender, COUNT(*) c FROM patients GROUP BY gender ORDER BY c DESC");
      while ($row = $r->fetch_assoc()):
          $pct = $totalPatients > 0 ? round($row['c'] / $totalPatients * 100, 1) : 0;
      ?>
        <tr>
          <td><span class="badge badge-<?= $row['gender'] ?>"><?= $row['gender'] ?></span></td>
          <td><?= $row['c'] ?></td>
          <td>
            <?= $pct ?>%
            <div class="pct-bar-wrap"><div class="pct-bar" style="width:<?= $pct ?>%;"></div></div>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Age Groups -->
    <div class="section-head">Patient Age Groups</div>
    <table>
      <thead><tr><th>Age Group</th><th>Count</th></tr></thead>
      <tbody>
      <?php
      $ageSql = "SELECT
          CASE
            WHEN TIMESTAMPDIFF(YEAR,dob,CURDATE()) < 18 THEN 'Under 18'
            WHEN TIMESTAMPDIFF(YEAR,dob,CURDATE()) < 35 THEN '18 – 34'
            WHEN TIMESTAMPDIFF(YEAR,dob,CURDATE()) < 50 THEN '35 – 49'
            WHEN TIMESTAMPDIFF(YEAR,dob,CURDATE()) < 65 THEN '50 – 64'
            ELSE '65 and above'
          END AS grp, COUNT(*) c
          FROM patients GROUP BY grp ORDER BY MIN(TIMESTAMPDIFF(YEAR,dob,CURDATE()))";
      $r = $conn->query($ageSql);
      while ($row = $r->fetch_assoc()): ?>
        <tr><td><?= $row['grp'] ?></td><td><?= $row['c'] ?></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Top Professions -->
    <div class="section-head">Top Volunteer Professions</div>
    <table>
      <thead><tr><th>Profession</th><th>Count</th></tr></thead>
      <tbody>
      <?php
      $r = $conn->query("SELECT COALESCE(NULLIF(profession,''),'(Unspecified)') prof, COUNT(*) c FROM volunteers GROUP BY prof ORDER BY c DESC LIMIT 8");
      while ($row = $r->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($row['prof']) ?></td><td><?= $row['c'] ?></td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Monthly Trends -->
    <div class="section-head">Monthly Patient Registrations (Last 12 Months)</div>
    <table>
      <thead><tr><th>Month</th><th>New Patients</th></tr></thead>
      <tbody>
      <?php for ($i = 11; $i >= 0; $i--):
          $month = date('Y-m', strtotime("-$i months"));
          $label = date('F Y', strtotime("-$i months"));
          $st = $conn->prepare("SELECT COUNT(*) FROM patients WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
          $st->bind_param("s", $month); $st->execute();
          $cnt = (int) $st->get_result()->fetch_row()[0]; ?>
        <tr><td><?= $label ?></td><td><?= $cnt ?></td></tr>
      <?php endfor; ?>
      </tbody>
    </table>
    <?php
}

echo $footer;
$html = $css . ob_get_clean();
$conn->close();

// ── Render PDF ────────────────────────────────────────────────────────────────
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('chroot', realpath(__DIR__ . '/..'));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$titles = [
    'events'    => 'Outreach_Events',
    'patients'  => 'Patient_Records',
    'volunteers'=> 'Volunteer_Roster',
    'summary'   => 'Summary_Report',
];
$filename = 'MedOutreach_' . $titles[$type] . '_' . date('Y-m-d') . '.pdf';

$dompdf->stream($filename, ['Attachment' => true]);
