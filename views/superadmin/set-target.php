<?php
require_once '../../config/session.php';
$user = requireAuth(['superadmin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set Target (OPCR) | CSU-Piat Super Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script>
  window.SESSION_USER = <?= json_encode($user, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
  const API_BASE = '<?= BASE_URL ?>api/';
  </script>
  <style>
    .ipcr-section-header {
      background: linear-gradient(135deg, #821131, #C7253E);
      color: #fff;
      padding: 0.6rem 1rem;
      border-radius: 6px 6px 0 0;
      font-weight: 600;
      font-size: 0.88rem;
      letter-spacing: 0.03em;
    }
    .ipcr-section-header .btn-light {
      font-size: 0.75rem;
      padding: 2px 10px;
      border-radius: 4px;
    }
    .opcr-summary-bar {
      background: #FFF4E6;
      border: 1px solid #FABC3F55;
      border-radius: 8px;
      padding: 0.75rem 1.25rem;
      font-size: 0.85rem;
    }
    .print-header { display: none; }
    @media print {
      .no-print { display: none !important; }
      .print-header { display: block; text-align: center; margin-bottom: 1rem; }
      .print-header h4 { font-size: 14pt; font-weight: 700; }
      .print-header p { font-size: 10pt; margin: 0; }
      .ipcr-section-header { background: #333 !important; -webkit-print-color-adjust: exact; }
      body { font-size: 10pt; }
      table { font-size: 9pt; }
    }
    .avg-row td { background: #FFF4E6 !important; font-weight: 600; }
    .rating-badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
<div id="toast-container"></div>
<div id="sidebar-container"></div>
<div id="navbar-container"></div>

<main class="main-content" id="mainContent">

  <!-- Print Header (hidden on screen) -->
  <div class="print-header">
    <h4>CAGAYAN STATE UNIVERSITY — PIAT CAMPUS</h4>
    <p>Office Performance Commitment and Review (OPCR)</p>
    <p id="printDeptHeader">Office of the Campus Executive Officer</p>
    <hr>
  </div>

  <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
      <h2><i class="fa-solid fa-bullseye me-2 text-primary"></i>OPCR Form</h2>
      <p class="mb-0">Office Performance Commitment and Review | Institutional Level (Campus Executive Officer) | CSU-Piat</p>
    </div>
    <div class="d-flex gap-2 flex-wrap no-print">
      <button class="btn btn-outline-primary btn-sm" id="btnViewEvidence" onclick="openEvidenceModal()">
        <i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-primary text-white ms-1" id="evidenceCountBadge">0</span>
      </button>
      <button class="btn btn-outline-primary btn-sm" id="btnUploadEvidence" onclick="openUploadModal()">
        <i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Evidence
      </button>
      <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
        <i class="fa-solid fa-print me-1"></i>Print
      </button>
      <button class="btn btn-outline-primary btn-sm" id="editBtn" onclick="enableEdit()" style="display:none">
        <i class="fa-solid fa-pen me-1"></i>Edit
      </button>
      <button class="btn btn-outline-primary btn-sm" id="btnSaveDraft" onclick="saveOPCR('draft')">
        <i class="fa-solid fa-floppy-disk me-1"></i>Save Draft
      </button>
      <button class="btn btn-primary btn-sm" id="confirmBtn" onclick="submitOPCR()">
        <i class="fa-solid fa-paper-plane me-1"></i>Confirm & Submit
      </button>
    </div>
  </div>

  <div id="noTimelineAlert" class="alert alert-warning d-none no-print" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>
    <strong>No active submission period.</strong> Please open an academic timeline in Settings to submit official OPCR entries.
  </div>

  <!-- Status Summary Banner -->
  <div class="opcr-summary-bar mb-3 d-flex align-items-center gap-3 flex-wrap">
    <span><i class="fa-solid fa-circle-info text-primary me-1"></i><strong>Status:</strong> <span id="statusBadge" class="badge bg-secondary ms-1">Draft</span></span>
    <span><i class="fa-solid fa-calendar text-primary me-1"></i><strong>Period:</strong> <span id="summaryPeriod">—</span></span>
    <span><i class="fa-solid fa-star text-primary me-1"></i><strong>Final Average:</strong> <span id="summaryRating">—</span></span>
    <span class="ms-auto text-muted" style="font-size:0.78rem">Last saved: <span id="lastSaved">Not yet saved</span></span>
  </div>

  <!-- Form Header / Commitment Details -->
  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-id-card me-2 text-primary"></i>Commitment Details</h6></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-500">Office / Unit</label>
          <input type="text" class="form-control bg-light" id="opcrOffice" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-500">Name of Campus Executive Officer</label>
          <input type="text" class="form-control bg-light" id="opcrName" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-500">Position / Designation</label>
          <input type="text" class="form-control bg-light" id="opcrPosition" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-500">Rating Period <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="opcrPeriod" placeholder="e.g. January – June 2026">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-500">Date Prepared</label>
          <input type="date" class="form-control" id="opcrDate">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-500">Rating Period</label>
          <select class="form-select" id="opcrSemester">
            <option value="January to June">January to June</option>
            <option value="July to December">July to December</option>
            <option value="Annual">Annual (January to December)</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- ── ETL Matrix Selection Card ──────────────────────────────────────── -->
  <div class="card mb-3 border-primary shadow-sm" id="etlMatrixCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2" style="background:#FFF4E6">
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-scale-balanced text-primary fs-5"></i>
        <div>
          <h6 class="mb-0 fw-700 text-dark">WEIGHT According to ETL</h6>
          <small class="text-muted">Equivalent Teaching Load (ETL) & Category Weight Distribution Matrix</small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <label for="etlSelect" class="form-label mb-0 fw-600 text-nowrap" style="font-size:0.85rem">Select ETL:</label>
        <select class="form-select form-select-sm fw-600" id="etlSelect" style="width:140px;border-color:var(--primary)" onchange="onEtlSelectChange(this.value)">
          <option value="18 ETL">18 ETL</option>
          <option value="15 ETL">15 ETL</option>
          <option value="12 ETL">12 ETL</option>
          <option value="9 ETL">9 ETL</option>
          <option value="6 ETL">6 ETL</option>
          <option value="3 ETL">3 ETL</option>
          <option value="0 ETL" selected>0 ETL</option>
        </select>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle mb-0 etl-table" id="etlMatrixTable">
          <thead class="table-light">
            <tr style="font-size:0.83rem">
              <th class="text-start bg-light" style="width:190px">WEIGHT According to ETL</th>
              <th class="etl-col-header" data-etl="18 ETL" onclick="selectEtl('18 ETL')" title="Click to select 18 ETL">18 ETL</th>
              <th class="etl-col-header" data-etl="15 ETL" onclick="selectEtl('15 ETL')" title="Click to select 15 ETL">15 ETL</th>
              <th class="etl-col-header" data-etl="12 ETL" onclick="selectEtl('12 ETL')" title="Click to select 12 ETL">12 ETL</th>
              <th class="etl-col-header" data-etl="9 ETL" onclick="selectEtl('9 ETL')" title="Click to select 9 ETL">9 ETL</th>
              <th class="etl-col-header" data-etl="6 ETL" onclick="selectEtl('6 ETL')" title="Click to select 6 ETL">6 ETL</th>
              <th class="etl-col-header" data-etl="3 ETL" onclick="selectEtl('3 ETL')" title="Click to select 3 ETL">3 ETL</th>
              <th class="etl-col-header active-etl-col" data-etl="0 ETL" onclick="selectEtl('0 ETL')" title="Click to select 0 ETL">0 ETL</th>
            </tr>
          </thead>
          <tbody style="font-size:0.84rem">
            <tr>
              <th class="text-start bg-light fw-600">Core Functions</th>
              <td class="etl-cell" data-etl="18 ETL" onclick="selectEtl('18 ETL')">10</td>
              <td class="etl-cell" data-etl="15 ETL" onclick="selectEtl('15 ETL')">20</td>
              <td class="etl-cell" data-etl="12 ETL" onclick="selectEtl('12 ETL')">30</td>
              <td class="etl-cell" data-etl="9 ETL" onclick="selectEtl('9 ETL')">40</td>
              <td class="etl-cell" data-etl="6 ETL" onclick="selectEtl('6 ETL')">50</td>
              <td class="etl-cell" data-etl="3 ETL" onclick="selectEtl('3 ETL')">60</td>
              <td class="etl-cell active-etl-cell" data-etl="0 ETL" onclick="selectEtl('0 ETL')">70</td>
            </tr>
            <tr>
              <th class="text-start bg-light fw-600">Strategic Priorities</th>
              <td class="etl-cell" data-etl="18 ETL" onclick="selectEtl('18 ETL')">50–65</td>
              <td class="etl-cell" data-etl="15 ETL" onclick="selectEtl('15 ETL')">40–55</td>
              <td class="etl-cell" data-etl="12 ETL" onclick="selectEtl('12 ETL')">35–50</td>
              <td class="etl-cell" data-etl="9 ETL" onclick="selectEtl('9 ETL')">30–40</td>
              <td class="etl-cell" data-etl="6 ETL" onclick="selectEtl('6 ETL')">25</td>
              <td class="etl-cell" data-etl="3 ETL" onclick="selectEtl('3 ETL')">20</td>
              <td class="etl-cell active-etl-cell" data-etl="0 ETL" onclick="selectEtl('0 ETL')">15</td>
            </tr>
            <tr>
              <th class="text-start bg-light fw-600">Support Functions</th>
              <td class="etl-cell" data-etl="18 ETL" onclick="selectEtl('18 ETL')">25–40</td>
              <td class="etl-cell" data-etl="15 ETL" onclick="selectEtl('15 ETL')">25–40</td>
              <td class="etl-cell" data-etl="12 ETL" onclick="selectEtl('12 ETL')">20–35</td>
              <td class="etl-cell" data-etl="9 ETL" onclick="selectEtl('9 ETL')">20–30</td>
              <td class="etl-cell" data-etl="6 ETL" onclick="selectEtl('6 ETL')">25</td>
              <td class="etl-cell" data-etl="3 ETL" onclick="selectEtl('3 ETL')">20</td>
              <td class="etl-cell active-etl-cell" data-etl="0 ETL" onclick="selectEtl('0 ETL')">15</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Active Weights summary bar / controls -->
      <div class="p-2 px-3 bg-light border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size:0.83rem">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <span class="text-muted"><i class="fa-solid fa-check-circle text-success me-1"></i>Active Preset: <strong id="activeEtlLabel" class="text-primary">0 ETL</strong></span>
          <span class="badge bg-white text-dark border px-2 py-1">Core: <strong id="dispWeightCore" class="text-primary">70%</strong> (0.70)</span>
          <span class="badge bg-white text-dark border px-2 py-1">Strategic: <strong id="dispWeightStrategic" class="text-primary">15%</strong> (0.15)</span>
          <span class="badge bg-white text-dark border px-2 py-1">Support: <strong id="dispWeightSupport" class="text-primary">15%</strong> (0.15)</span>
          <span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-equals me-1"></i>Total: <strong id="dispWeightTotal">100%</strong></span>
        </div>
        <div id="etlRangeAdjuster" class="d-none align-items-center gap-2">
          <small class="text-muted">Adjust Strategic %:</small>
          <input type="number" id="inpStrategicWeight" class="form-control form-control-sm text-center" style="width:65px" min="15" max="65" step="1" oninput="onCustomStrategicInput(this.value)">
          <small class="text-muted">&rarr; Support auto-balances to 100%</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Instructions Accordion -->
  <div class="accordion mb-3 no-print" id="instructionAccordion">
    <div class="accordion-item border-0 shadow-sm">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#instrBody" style="font-size:0.85rem;background:#FFF4E6">
          <i class="fa-solid fa-circle-question me-2 text-primary"></i> How to fill out this OPCR form
        </button>
      </h2>
      <div id="instrBody" class="accordion-collapse collapse">
        <div class="accordion-body" style="font-size:0.83rem">
          <ul class="mb-0">
            <li><strong>MFO / PAP</strong> — Major Final Output / Program, Activity, or Project (e.g., Instruction, Research, Extension, Management of Resources).</li>
            <li><strong>Success Indicator</strong> — A specific, measurable commitment (Target + Measure).</li>
            <li><strong>Target</strong> — The numeric goal or percentage you commit to achieve.</li>
            <li><strong>Budget Required</strong> — Estimated budget in Philippine Peso (0 if none needed).</li>
            <li><strong>Actual Accomplishment</strong> — Actual accomplishment percentage (1-100%) or numeric output.</li>
            <li><strong>Rating Scale</strong> — 5: Outstanding (4.50–5.00), 4: Very Satisfactory (3.50–4.49), 3: Satisfactory (2.50–3.49), 2: Unsatisfactory (1.50–2.49), 1: Poor (1.00–1.49).</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- ── A. CORE FUNCTION ── -->
  <div class="mb-3">
    <div class="ipcr-section-header d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-star me-2"></i>A. CORE FUNCTIONS</span>
      <button class="btn btn-sm btn-light no-print" onclick="addRow('coreBody')">
        <i class="fa-solid fa-plus me-1"></i>Add Row
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0" id="coreTable">
        <thead class="table-light" style="font-size:0.8rem">
          <tr>
            <th style="min-width:140px">MFO / PAP</th>
            <th style="min-width:200px">Success Indicator</th>
            <th style="min-width:90px">Target</th>
            <th style="min-width:90px">Budget (₱)</th>
            <th style="min-width:100px">Actual Acc.</th>
            <th style="width:65px">Q</th>
            <th style="width:65px">E</th>
            <th style="width:65px">T</th>
            <th style="width:75px">Average</th>
            <th style="min-width:110px">Remarks</th>
            <th style="width:140px;text-align:center">Evidence</th>
          </tr>
        </thead>
        <tbody id="coreBody"></tbody>
        <tfoot>
          <tr class="avg-row">
            <td colspan="8" class="text-end fw-600" style="font-size:0.83rem">
              Average Rating — Core Function: <span id="coreAvg" class="fw-700 text-dark ms-1">—</span>
              <span class="mx-2 text-muted">|</span>
              Weighted Average (<span id="coreFormulaText">Average × 0.70</span>):
            </td>
            <td id="coreWeightedAvg" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
            <td></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- ── B. STRATEGIC FUNCTION ── -->
  <div class="mb-3">
    <div class="ipcr-section-header d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-chess me-2"></i>B. STRATEGIC FUNCTIONS</span>
      <button class="btn btn-sm btn-light no-print" onclick="addRow('strategicBody')">
        <i class="fa-solid fa-plus me-1"></i>Add Row
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0" id="strategicTable">
        <thead class="table-light" style="font-size:0.8rem">
          <tr>
            <th style="min-width:140px">MFO / PAP</th>
            <th style="min-width:200px">Success Indicator</th>
            <th style="min-width:90px">Target</th>
            <th style="min-width:90px">Budget (₱)</th>
            <th style="min-width:100px">Actual Acc.</th>
            <th style="width:65px">Q</th>
            <th style="width:65px">E</th>
            <th style="width:65px">T</th>
            <th style="width:75px">Average</th>
            <th style="min-width:110px">Remarks</th>
            <th style="width:140px;text-align:center">Evidence</th>
          </tr>
        </thead>
        <tbody id="strategicBody"></tbody>
        <tfoot>
          <tr class="avg-row">
            <td colspan="8" class="text-end fw-600" style="font-size:0.83rem">
              Average Rating — Strategic Function: <span id="strategicAvg" class="fw-700 text-dark ms-1">—</span>
              <span class="mx-2 text-muted">|</span>
              Weighted Average (<span id="strategicFormulaText">Average × 0.15</span>):
            </td>
            <td id="strategicWeightedAvg" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
            <td></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- ── C. SUPPORT FUNCTION ── -->
  <div class="mb-3">
    <div class="ipcr-section-header d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-hands-helping me-2"></i>C. SUPPORT FUNCTIONS</span>
      <button class="btn btn-sm btn-light no-print" onclick="addRow('supportBody')">
        <i class="fa-solid fa-plus me-1"></i>Add Row
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered mb-0" id="supportTable">
        <thead class="table-light" style="font-size:0.8rem">
          <tr>
            <th style="min-width:140px">MFO / PAP</th>
            <th style="min-width:200px">Success Indicator</th>
            <th style="min-width:90px">Target</th>
            <th style="min-width:90px">Budget (₱)</th>
            <th style="min-width:100px">Actual Acc.</th>
            <th style="width:65px">Q</th>
            <th style="width:65px">E</th>
            <th style="width:65px">T</th>
            <th style="width:75px">Average</th>
            <th style="min-width:110px">Remarks</th>
            <th style="width:140px;text-align:center">Evidence</th>
          </tr>
        </thead>
        <tbody id="supportBody"></tbody>
        <tfoot>
          <tr class="avg-row">
            <td colspan="8" class="text-end fw-600" style="font-size:0.83rem">
              Average Rating — Support Function: <span id="supportAvg" class="fw-700 text-dark ms-1">—</span>
              <span class="mx-2 text-muted">|</span>
              Weighted Average (<span id="supportFormulaText">Average × 0.15</span>):
            </td>
            <td id="supportWeightedAvg" class="text-center fw-700 text-primary" style="font-size:0.88rem">—</td>
            <td></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- Computed Final Overall Rating -->
  <div class="card mb-4 border-primary">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#FFF4E6">
      <div>
        <h6 class="mb-0 fw-700"><i class="fa-solid fa-calculator me-2 text-primary"></i>Computed Overall Rating</h6>
        <small class="text-muted">Weighted Average of Core, Strategic, and Support Functions based on <span id="summaryEtlBadge" class="badge bg-primary">0 ETL</span></small>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="fs-4 fw-700 text-primary" id="finalAvgDisplay">—</span>
        <span id="finalRatingLabel" class="rating-badge bg-secondary text-white">Not yet rated</span>
      </div>
    </div>
  </div>

  <!-- Certification & Signatures Card -->
  <div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-pen-nib me-2 text-primary"></i>Certification & Signatures</h6></div>
    <div class="card-body">
      <p style="font-size:0.83rem" class="mb-3">I hereby commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for the covered period.</p>
      <div class="row g-4">
        <div class="col-md-4 text-center">
          <div style="border-bottom:1px solid #333;margin-bottom:4px;height:48px"></div>
          <strong style="font-size:0.82rem" id="sigName"></strong><br>
          <small class="text-muted">Campus Executive Officer (Ratee)</small>
        </div>
        <div class="col-md-4 text-center">
          <div style="border-bottom:1px solid #333;margin-bottom:4px;height:48px"></div>
          <strong style="font-size:0.82rem">VP FOR ACADEMIC AFFAIRS</strong><br>
          <small class="text-muted">CSU System Administration (Rater)</small>
        </div>
        <div class="col-md-4 text-center">
          <div style="border-bottom:1px solid #333;margin-bottom:4px;height:48px"></div>
          <strong style="font-size:0.82rem">UNIVERSITY PRESIDENT</strong><br>
          <small class="text-muted">Approving Authority</small>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end no-print mb-4 flex-wrap">
    <button class="btn btn-outline-primary" id="btnViewEvidence2" onclick="openEvidenceModal()"><i class="fa-solid fa-paperclip me-1"></i>View Evidence <span class="badge bg-primary text-white ms-1" id="evidenceCountBadge2">0</span></button>
    <button class="btn btn-outline-primary" id="btnUploadEvidence2" onclick="openUploadModal()"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Evidence</button>
    <button class="btn btn-outline-secondary" onclick="showPrintPreview()"><i class="fa-solid fa-print me-1"></i>Print Preview</button>
    <button class="btn btn-outline-primary" id="editBtn2" onclick="enableEdit()" style="display:none"><i class="fa-solid fa-pen me-1"></i>Edit</button>
    <button class="btn btn-outline-primary" id="btnSaveDraft2" onclick="saveOPCR('draft')"><i class="fa-solid fa-floppy-disk me-1"></i>Save Draft</button>
    <button class="btn btn-primary" id="confirmBtn2" onclick="submitOPCR()"><i class="fa-solid fa-paper-plane me-1"></i>Confirm & Submit</button>
  </div>

</main>

<!-- View Evidence Modal -->
<div class="modal fade" id="evidenceModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-paperclip me-2"></i>Supporting Evidence & Documents — <span id="evidenceModalUser"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <div id="evidenceFilterBadge" class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">All Evidence</div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnShowAllEv" onclick="renderEvidenceList(currentEvidence, 'All Evidence')"><i class="fa-solid fa-list me-1"></i>Show All</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openUploadModalFromEvidence()"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Document</button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:40px">#</th>
                <th>File Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Size</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="evidenceModalTable"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Upload Evidence Modal -->
<div class="modal fade" id="uploadEvidenceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Upload Supporting Evidence</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="quickUploadForm" onsubmit="submitQuickUpload(event)">
          <div class="mb-3">
            <label class="form-label fw-600">Category</label>
            <select class="form-select form-select-sm" id="quickUploadCategory">
              <option value="core">Core Function</option>
              <option value="strategic">Strategic Function</option>
              <option value="support">Support Function</option>
              <option value="other">Other / Miscellaneous</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">MFO / Document Description</label>
            <input type="text" class="form-control form-control-sm" id="quickUploadDesc" placeholder="e.g. Instruction, Syllabi, Accreditation Report...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Choose File(s)</label>
            <input type="file" class="form-control form-control-sm" id="quickUploadFiles" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.gif,.txt,.zip,.csv" required>
            <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG, XLSX (Max: 20MB per file)</small>
          </div>
          <div id="quickUploadStatus" class="alert alert-info py-2 d-none" style="font-size:0.83rem"></div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitQuickUpload"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload & Attach</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="footer-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/auth.js"></script>
<script src="../../assets/js/components.js"></script>
<script>
  const session = requireAuth(['superadmin']);
  initLayout('superadmin', 'set-target', [{ label: 'OPCR Form' }]);

  const STORAGE_KEY = 'csu_piat_superadmin_opcr';
  let activeTimeline = null;
  let existingOpcrId = 0;
  let isReadOnly = false;
  let currentEvidence = [];
  let _evidenceModal = null;

  // Pre-fill header info
  document.getElementById('opcrOffice').value = 'Office of the Campus Executive Officer — CSU Piat';
  document.getElementById('opcrName').value = session.name || 'HITLER C. DANGATAN, Ph.D.';
  document.getElementById('opcrPosition').value = session.position || 'Campus Executive Officer';
  document.getElementById('opcrDate').value = new Date().toISOString().split('T')[0];
  document.getElementById('sigName').textContent = (session.name || 'HITLER C. DANGATAN, Ph.D.').toUpperCase();
  document.getElementById('printDeptHeader').textContent = 'Office of the Campus Executive Officer — CSU Piat';

  // Default Institutional OPCR rows for Campus Executive Officer
  const DEFAULT_CORE = [
    { mfo: 'Instruction', success_indicator: 'At least 90% of teaching loads properly distributed and monitored per semester', target: '90%', budget: '0', measure: 'Q/T', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Instruction', success_indicator: '100% of faculty with complete and submitted course syllabi by Week 2 of each semester', target: '100%', budget: '0', measure: 'Q/T', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Research', success_indicator: 'At least 5 completed and documented research outputs published or presented per year', target: '5', budget: '150000', measure: 'Q/Qn', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Research', success_indicator: 'At least 80% of ongoing research projects with completed monitoring reports', target: '80%', budget: '0', measure: 'Q/T', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Extension', success_indicator: 'At least 3 community extension programs implemented per semester with documented beneficiaries', target: '3', budget: '80000', measure: 'Q/Qn/E', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Extension', success_indicator: '100% of extension programs with submitted Terminal Reports within 15 days after completion', target: '100%', budget: '0', measure: 'T', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Production', success_indicator: 'At least 2 production or income-generating projects maintained or established within the campus', target: '2', budget: '50000', measure: 'Q/Qn', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' }
  ];

  const DEFAULT_STRATEGIC = [
    { mfo: 'AACCUP Accreditation', success_indicator: 'Achieve at least Level III accreditation status for targeted programs by 2nd Semester 2026', target: 'Level III', budget: '200000', measure: 'Q', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Faculty Development', success_indicator: 'At least 70% of teaching personnel pursuing graduate studies or with relevant professional training', target: '70%', budget: '100000', measure: 'Q/Qn', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Enrollment Growth', success_indicator: 'Achieve at least 5% increase in total student enrollment compared to previous year', target: '5%', budget: '30000', measure: 'Qn/E', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Linkages & MOAs', success_indicator: 'At least 3 new Memoranda of Agreement (MOAs) signed with LGUs, NGOs, or industry partners', target: '3', budget: '10000', measure: 'Q/Qn', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Gender & Development', success_indicator: 'GAD Plan and Budget submitted and approved; at least 2 GAD activities conducted per semester', target: '100%', budget: '40000', measure: 'Q/T', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' }
  ];

  const DEFAULT_SUPPORT = [
    { mfo: 'Administrative Reports', success_indicator: '100% of required reports (CHED, DepEd, CSC, DBM) submitted on or before deadline', target: '100%', budget: '0', measure: 'T/Q', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Meetings & Committees', success_indicator: 'At least 2 Administrative Council meetings conducted per month with documented minutes', target: '2/month', budget: '0', measure: 'T/Qn', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Budget & Finance', success_indicator: '100% of financial reports and liquidations submitted within 5 working days of completion', target: '100%', budget: '0', measure: 'T/Q', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'HR & Personnel', success_indicator: 'All personnel IPCR/OPCR forms reviewed and endorsed within 10 days of receipt', target: '100%', budget: '0', measure: 'T/Q', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' },
    { mfo: 'Campus Maintenance', success_indicator: 'At least 90% of maintenance requests acted upon within 3 working days', target: '90%', budget: '120000', measure: 'T/E', accomplishment: '', q_rating: '', e_rating: '', t_rating: '', rating: '', remarks: '' }
  ];

  // ── ETL Weight Configuration & State ─────────────────────────────────────
  const ETL_PRESETS = {
    '18 ETL': { core: 10, strategicMin: 50, strategicMax: 65, strategicDef: 60, supportDef: 30 },
    '15 ETL': { core: 20, strategicMin: 40, strategicMax: 55, strategicDef: 45, supportDef: 35 },
    '12 ETL': { core: 30, strategicMin: 35, strategicMax: 50, strategicDef: 40, supportDef: 30 },
    '9 ETL':  { core: 40, strategicMin: 30, strategicMax: 40, strategicDef: 35, supportDef: 25 },
    '6 ETL':  { core: 50, strategicMin: 25, strategicMax: 25, strategicDef: 25, supportDef: 25 },
    '3 ETL':  { core: 60, strategicMin: 20, strategicMax: 20, strategicDef: 20, supportDef: 20 },
    '0 ETL':  { core: 70, strategicMin: 15, strategicMax: 15, strategicDef: 15, supportDef: 15 }
  };

  let currentEtl = '0 ETL';
  let activeWeights = { core: 70, strategic: 15, support: 15 };

  function selectEtl(etlKey, customWeights = null) {
    if (!ETL_PRESETS[etlKey]) etlKey = '0 ETL';
    currentEtl = etlKey;
    const preset = ETL_PRESETS[etlKey];

    if (customWeights && customWeights.core !== undefined) {
      activeWeights.core = parseFloat(customWeights.core) || preset.core;
      activeWeights.strategic = parseFloat(customWeights.strategic) || preset.strategicDef;
      activeWeights.support = parseFloat(customWeights.support) || preset.supportDef;
    } else {
      activeWeights = {
        core: preset.core,
        strategic: preset.strategicDef,
        support: preset.supportDef
      };
    }

    // Sync select dropdown
    const etlSel = document.getElementById('etlSelect');
    if (etlSel && etlSel.value !== etlKey) etlSel.value = etlKey;

    // Highlight active column in table
    document.querySelectorAll('#etlMatrixTable .etl-col-header').forEach(th => {
      th.classList.toggle('active-etl-col', th.dataset.etl === etlKey);
    });
    document.querySelectorAll('#etlMatrixTable .etl-cell').forEach(td => {
      td.classList.toggle('active-etl-cell', td.dataset.etl === etlKey);
    });

    // Update active weight display badges
    const lbl = document.getElementById('activeEtlLabel');
    if (lbl) lbl.textContent = etlKey;

    const bCore = document.getElementById('dispWeightCore');
    const bStrat = document.getElementById('dispWeightStrategic');
    const bSupp = document.getElementById('dispWeightSupport');
    const bTot = document.getElementById('dispWeightTotal');
    if (bCore) bCore.textContent = activeWeights.core + '%';
    if (bStrat) bStrat.textContent = activeWeights.strategic + '%';
    if (bSupp) bSupp.textContent = activeWeights.support + '%';
    if (bTot) bTot.textContent = (activeWeights.core + activeWeights.strategic + activeWeights.support) + '%';

    // Update section footer formula labels
    const fCore = document.getElementById('coreFormulaText');
    const fStrat = document.getElementById('strategicFormulaText');
    const fSupp = document.getElementById('supportFormulaText');
    if (fCore) fCore.textContent = `Average × ${(activeWeights.core / 100).toFixed(2)}`;
    if (fStrat) fStrat.textContent = `Average × ${(activeWeights.strategic / 100).toFixed(2)}`;
    if (fSupp) fSupp.textContent = `Average × ${(activeWeights.support / 100).toFixed(2)}`;

    // Update summary badge
    const sumBadge = document.getElementById('summaryEtlBadge');
    if (sumBadge) sumBadge.textContent = etlKey;

    // Range adjuster for flexible ETLs
    const rangeDiv = document.getElementById('etlRangeAdjuster');
    const rangeInp = document.getElementById('inpStrategicWeight');
    if (rangeDiv && rangeInp) {
      if (preset.strategicMin < preset.strategicMax) {
        rangeDiv.classList.remove('d-none');
        rangeDiv.classList.add('d-flex');
        rangeInp.min = preset.strategicMin;
        rangeInp.max = preset.strategicMax;
        rangeInp.value = activeWeights.strategic;
      } else {
        rangeDiv.classList.add('d-none');
        rangeDiv.classList.remove('d-flex');
      }
    }

    if (typeof computeAverages === 'function') {
      computeAverages();
    }
  }

  function onEtlSelectChange(val) {
    selectEtl(val);
  }

  function onCustomStrategicInput(val) {
    const preset = ETL_PRESETS[currentEtl];
    if (!preset) return;
    let sVal = parseFloat(val);
    if (isNaN(sVal)) return;
    if (sVal < preset.strategicMin) sVal = preset.strategicMin;
    if (sVal > preset.strategicMax) sVal = preset.strategicMax;
    activeWeights.strategic = sVal;
    activeWeights.support = 100 - activeWeights.core - activeWeights.strategic;

    const bStrat = document.getElementById('dispWeightStrategic');
    const bSupp = document.getElementById('dispWeightSupport');
    if (bStrat) bStrat.textContent = activeWeights.strategic + '%';
    if (bSupp) bSupp.textContent = activeWeights.support + '%';

    const fStrat = document.getElementById('strategicFormulaText');
    const fSupp = document.getElementById('supportFormulaText');
    if (fStrat) fStrat.textContent = `Average × ${(activeWeights.strategic / 100).toFixed(2)}`;
    if (fSupp) fSupp.textContent = `Average × ${(activeWeights.support / 100).toFixed(2)}`;

    if (typeof computeAverages === 'function') {
      computeAverages();
    }
  }

  function updatePeriodSummary() {
    const p = document.getElementById('opcrPeriod').value;
    document.getElementById('summaryPeriod').textContent = p || '—';
  }
  document.getElementById('opcrPeriod').addEventListener('input', updatePeriodSummary);

  function setStatus(status) {
    const badge = document.getElementById('statusBadge');
    const map = {
      draft:       ['bg-secondary', 'Draft'],
      pending:     ['bg-warning text-dark', 'Pending Review'],
      reviewed:    ['bg-primary text-white', 'Reviewed'],
      approved:    ['bg-success', 'Approved / Confirmed'],
      disapproved: ['bg-danger', 'Disapproved']
    };
    const [cls, label] = map[status] || ['bg-secondary', status];
    badge.className = `badge ${cls} ms-1`;
    badge.textContent = label;
  }

  function setReadOnly(on) {
    isReadOnly = on;
    const allInputs = document.querySelectorAll('#coreBody input, #strategicBody input, #supportBody input, #opcrPeriod, #opcrDate, #opcrSemester');
    allInputs.forEach(i => i.disabled = on);
    const editBtns = [document.getElementById('editBtn'), document.getElementById('editBtn2')];
    editBtns.forEach(b => { if (b) b.style.display = on ? 'inline-flex' : 'none'; });
    const actionBtns = [document.getElementById('confirmBtn'), document.getElementById('confirmBtn2'), document.getElementById('btnSaveDraft'), document.getElementById('btnSaveDraft2')];
    actionBtns.forEach(b => { if (b) b.style.display = on ? 'none' : 'inline-flex'; });
    const addBtns = document.querySelectorAll('.ipcr-section-header button');
    addBtns.forEach(b => b.disabled = on);
    const etlSel = document.getElementById('etlSelect');
    if (etlSel) etlSel.disabled = on;
    const inpStrat = document.getElementById('inpStrategicWeight');
    if (inpStrat) inpStrat.disabled = on;
    document.querySelectorAll('#etlMatrixTable .etl-col-header, #etlMatrixTable .etl-cell').forEach(el => {
      el.style.pointerEvents = on ? 'none' : '';
      el.style.opacity = on ? '0.75' : '';
    });
  }

  function enableEdit() {
    confirmModal('Allow editing of this OPCR? You can make changes and re-submit or save.', 'Enable Edit', () => {
      setReadOnly(false);
      setStatus('draft');
      showToast('OPCR is now editable. Remember to click Confirm & Submit after making changes.', 'info');
    });
  }


  function esc(val) {
    return (val || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function getMatchingEvidence(categoryKey, mfoText) {
    const list = currentEvidence || [];
    const catSearch = (categoryKey || '').toLowerCase().trim();
    const mfoSearch = (mfoText || '').toLowerCase().trim();

    if (mfoSearch) {
      return list.filter(file => {
        const fCat = (file.category || '').toLowerCase().trim();
        const fMfo = (file.mfo || '').toLowerCase().trim();
        const fDesc = (file.description || '').toLowerCase().trim();

        // If file category is specified and conflicts with section, skip
        if (catSearch && fCat && fCat !== 'other' && fCat !== 'evidence' && fCat !== catSearch) {
          return false;
        }

        // Match against explicit mfo field
        if (fMfo && (fMfo === mfoSearch || fMfo.includes(mfoSearch) || mfoSearch.includes(fMfo))) {
          return true;
        }

        // Match against description if not default placeholder
        if (fDesc && fDesc !== 'uploaded evidence' && (fDesc === mfoSearch || fDesc.includes(mfoSearch) || mfoSearch.includes(fDesc))) {
          return true;
        }

        return false;
      });
    }

    if (catSearch && catSearch !== 'all') {
      return list.filter(file => {
        const fCat = (file.category || '').toLowerCase().trim();
        return fCat === catSearch || (catSearch === 'core' && fCat.includes('core')) || (catSearch === 'strategic' && fCat.includes('strat')) || (catSearch === 'support' && fCat.includes('supp'));
      });
    }

    return list;
  }

  function updateRowEvidenceBtn(inputEl) {
    const tr = inputEl.closest('tr');
    if (!tr) return;
    const cat = tr.dataset.category || 'core';
    const mfoVal = inputEl.value.trim();
    const matchedFiles = getMatchingEvidence(cat, mfoVal);
    const count = matchedFiles.length;
    const mfoSafe = esc(mfoVal);
    const cell = tr.querySelector('.evidence-cell');
    if (cell) {
      const viewBtn = count > 0
        ? `<button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${cat}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>View (${count})</span></button>`
        : `<button type="button" class="btn btn-sm btn-outline-secondary opacity-75 d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${cat}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>0 Files</span></button>`;
      const uploadBtn = `<button type="button" class="btn btn-sm btn-outline-primary" title="Upload Evidence for this MFO" onclick="openUploadModalFor('${cat}', '${mfoSafe}')"><i class="fa-solid fa-cloud-arrow-up"></i></button>`;
      cell.innerHTML = `<div class="d-inline-flex align-items-center justify-content-center gap-1">${viewBtn}${uploadBtn}</div>`;
    }
  }

  function createRow(data = {}, categoryKey = 'core') {
    const tr = document.createElement('tr');
    tr.dataset.kpiId = data.kpi_id || data.kpiId || '';
    tr.dataset.category = categoryKey;
    const q = data.q_rating !== undefined ? data.q_rating : (data.q || '');
    const e = data.e_rating !== undefined ? data.e_rating : (data.e || '');
    const t = data.t_rating !== undefined ? data.t_rating : (data.t || '');
    const avg = parseFloat(data.rating) || 0;
    const actual = data.actual !== undefined ? data.actual : (data.accomplishment !== undefined ? data.accomplishment : '');
    const remarks = data.remarks || (avg > 0 ? getAdjectivalText(avg) : '');
    const mfoVal = data.mfo || '';
    const mfoSafe = esc(mfoVal);
    const matchedFiles = getMatchingEvidence(categoryKey, mfoVal);
    const count = matchedFiles.length;

    const viewBtn = count > 0
      ? `<button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>View (${count})</span></button>`
      : `<button type="button" class="btn btn-sm btn-outline-secondary opacity-75 d-inline-flex align-items-center gap-1" onclick="openEvidenceModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-paperclip"></i><span>0 Files</span></button>`;
    const uploadBtn = `<button type="button" class="btn btn-sm btn-outline-primary" title="Upload Evidence for this MFO" onclick="openUploadModalFor('${categoryKey}', '${mfoSafe}')"><i class="fa-solid fa-cloud-arrow-up"></i></button>`;
    const evidenceBtn = `<div class="d-inline-flex align-items-center justify-content-center gap-1">${viewBtn}${uploadBtn}</div>`;

    const targetVal = (data.target || '').trim();
    const isNA = /^(n\/?a|not applicable|none|n\s*a)$/i.test(targetVal);
    const pctMatch = targetVal.match(/(\d+)\s*%/);
    const isNum = /^\d+$/.test(targetVal);
    const accType = (isNA || (!pctMatch && !isNum)) ? 'text' : 'number';
    const accMode = (isNA || (!pctMatch && !isNum)) ? 'text' : (pctMatch ? 'percentage' : 'numeric');
    const accMax = pctMatch ? parseInt(pctMatch[1], 10) : (isNum ? parseInt(targetVal, 10) : '');
    const accPh = (isNA || (!pctMatch && !isNum)) ? (isNA ? 'Enter text / N/A' : 'Accomplishment...') : (pctMatch ? `1-${accMax}%` : `1-${accMax}`);
    const maxAttr = accMax ? `min="1" max="${accMax}" step="1"` : '';
    const pctAttr = pctMatch ? `data-max-pct="${accMax}"` : (isNum ? `data-max-num="${accMax}"` : '');

    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm mfo-input" value="${esc(mfoVal)}" placeholder="e.g. Instruction" oninput="updateRowEvidenceBtn(this)"></td>
      <td><input type="text" class="form-control form-control-sm si-input" value="${esc(data.success_indicator || data.successIndicator || '')}" placeholder="Success indicator..."></td>
      <td><input type="text" class="form-control form-control-sm target-input text-center" style="width:90px" value="${esc(data.target || '')}" placeholder="100%" oninput="syncRowTargetAndAcc(this.closest('tr'))"></td>
      <td><input type="number" class="form-control form-control-sm budget-input text-end" style="width:90px" value="${data.budget || 0}" min="0" placeholder="0"></td>
      <td><input type="${accType}" class="form-control form-control-sm acc-input text-center" ${maxAttr} data-mode="${accMode}" ${pctAttr} style="width:90px" value="${esc(actual)}" placeholder="${accPh}" oninput="validateAccInput(this)" onkeydown="enforceDigitsOnly(event)"></td>
      <td><input type="number" class="form-control form-control-sm rating-q text-center" min="1" max="5" step="0.1" style="width:60px" value="${q}" placeholder="1-5" oninput="computeRowRating(this)"></td>
      <td><input type="number" class="form-control form-control-sm rating-e text-center" min="1" max="5" step="0.1" style="width:60px" value="${e}" placeholder="1-5" oninput="computeRowRating(this)"></td>
      <td><input type="number" class="form-control form-control-sm rating-t text-center" min="1" max="5" step="0.1" style="width:60px" value="${t}" placeholder="1-5" oninput="computeRowRating(this)"></td>
      <td class="text-center fw-700 row-avg" style="font-size:0.85rem;background:#fafafa">${avg > 0 ? avg.toFixed(2) : '-'}</td>
      <td><select class="form-select form-select-sm row-remarks" style="min-width:130px">${renderRemarksOptions(remarks)}</select></td>
      <td class="text-center evidence-cell">${evidenceBtn}</td>`;
    return tr;
  }

  function addRow(tbodyId) {
    const cat = tbodyId.replace('Body', '');
    document.getElementById(tbodyId).appendChild(createRow({}, cat));
    computeAverages();
  }

  function loadRows(tbodyId, items) {
    const cat = tbodyId.replace('Body', '');
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    (items || []).forEach(item => tbody.appendChild(createRow(item, cat)));
  }

  function getRows(tbodyId) {
    const rows = [];
    document.getElementById(tbodyId).querySelectorAll('tr').forEach(tr => {
      const mfoInp     = tr.querySelector('.mfo-input');
      const siInp      = tr.querySelector('.si-input');
      const targetInp  = tr.querySelector('.target-input');
      const budgetInp  = tr.querySelector('.budget-input');
      const accInp     = tr.querySelector('.acc-input');
      const qInp       = tr.querySelector('.rating-q');
      const eInp       = tr.querySelector('.rating-e');
      const tInp       = tr.querySelector('.rating-t');
      const avgCell    = tr.querySelector('.row-avg');
      const remarksInp = tr.querySelector('.row-remarks');

      const q = parseFloat(qInp?.value) || null;
      const e = parseFloat(eInp?.value) || null;
      const t = parseFloat(tInp?.value) || null;
      const a = parseFloat(avgCell?.textContent) || null;

      rows.push({
        kpi_id:            tr.dataset.kpiId || '',
        mfo:               mfoInp?.value.trim() || '',
        success_indicator: siInp?.value.trim() || '',
        target:            targetInp?.value.trim() || '',
        budget:            budgetInp?.value || '0',
        measure:           '',
        actual:            accInp?.value.trim() || '',
        q_rating:          q,
        e_rating:          e,
        t_rating:          t,
        rating:            a,
        remarks:           remarksInp?.value || ''
      });
    });
    return rows;
  }

  function computeRowRating(inputEl) {
    const tr = inputEl.closest('tr');
    if (!tr) return;
    const qInp = tr.querySelector('.rating-q');
    const eInp = tr.querySelector('.rating-e');
    const tInp = tr.querySelector('.rating-t');
    const avgCell = tr.querySelector('.row-avg');
    const remarksInp = tr.querySelector('.row-remarks');

    const vals = [qInp, eInp, tInp].map(i => parseFloat(i?.value)).filter(v => !isNaN(v) && v >= 1 && v <= 5);
    if (vals.length > 0) {
      const avg = vals.reduce((a, b) => a + b, 0) / vals.length;
      avgCell.textContent = avg.toFixed(2);
    } else {
      avgCell.textContent = '-';
    }
    computeAverages();
  }

  function computeAverages() {
    function getSectionAvg(tbodyId) {
      const avgCells = document.querySelectorAll(`#${tbodyId} .row-avg`);
      const vals = Array.from(avgCells).map(c => parseFloat(c.textContent)).filter(v => !isNaN(v) && v > 0);
      return vals.length > 0 ? (vals.reduce((a, b) => a + b, 0) / vals.length) : null;
    }

    const coreAvg = getSectionAvg('coreBody');
    const strategicAvg = getSectionAvg('strategicBody');
    const supportAvg = getSectionAvg('supportBody');

    const wCore = (activeWeights.core || 70) / 100;
    const wStrat = (activeWeights.strategic || 15) / 100;
    const wSupp = (activeWeights.support || 15) / 100;

    const coreWeighted = coreAvg !== null ? (coreAvg * wCore) : null;
    const strategicWeighted = strategicAvg !== null ? (strategicAvg * wStrat) : null;
    const supportWeighted = supportAvg !== null ? (supportAvg * wSupp) : null;

    // Update section footers
    const elCoreAvg = document.getElementById('coreAvg');
    const elCoreW = document.getElementById('coreWeightedAvg');
    if (elCoreAvg) elCoreAvg.textContent = coreAvg !== null ? coreAvg.toFixed(2) : '—';
    if (elCoreW) elCoreW.textContent = coreWeighted !== null ? coreWeighted.toFixed(2) : '—';

    const elStratAvg = document.getElementById('strategicAvg');
    const elStratW = document.getElementById('strategicWeightedAvg');
    if (elStratAvg) elStratAvg.textContent = strategicAvg !== null ? strategicAvg.toFixed(2) : '—';
    if (elStratW) elStratW.textContent = strategicWeighted !== null ? strategicWeighted.toFixed(2) : '—';

    const elSuppAvg = document.getElementById('supportAvg');
    const elSuppW = document.getElementById('supportWeightedAvg');
    if (elSuppAvg) elSuppAvg.textContent = supportAvg !== null ? supportAvg.toFixed(2) : '—';
    if (elSuppW) elSuppW.textContent = supportWeighted !== null ? supportWeighted.toFixed(2) : '—';

    // Compute overall weighted average
    let sumWeighted = 0;
    let activeWeightSum = 0;
    if (coreWeighted !== null) { sumWeighted += coreWeighted; activeWeightSum += wCore; }
    if (strategicWeighted !== null) { sumWeighted += strategicWeighted; activeWeightSum += wStrat; }
    if (supportWeighted !== null) { sumWeighted += supportWeighted; activeWeightSum += wSupp; }

    const finalAvg = activeWeightSum > 0 ? (sumWeighted / activeWeightSum) : null;

    const display = document.getElementById('finalAvgDisplay');
    const summary = document.getElementById('summaryRating');
    const label = document.getElementById('finalRatingLabel');

    if (finalAvg !== null && finalAvg > 0) {
      const valStr = finalAvg.toFixed(2);
      display.textContent = valStr;
      summary.textContent = valStr;
      if (finalAvg >= 4.5) { label.className = 'rating-badge bg-success text-white'; label.textContent = 'Outstanding'; label.style.background = ''; }
      else if (finalAvg >= 3.5) { label.className = 'rating-badge text-white'; label.style.background = '#E85C0D'; label.textContent = 'Very Satisfactory'; }
      else if (finalAvg >= 2.5) { label.className = 'rating-badge text-dark'; label.style.background = '#FABC3F'; label.textContent = 'Satisfactory'; }
      else if (finalAvg >= 1.5) { label.className = 'rating-badge bg-danger text-white'; label.style.background = ''; label.textContent = 'Unsatisfactory'; }
      else { label.className = 'rating-badge bg-dark text-white'; label.style.background = ''; label.textContent = 'Poor'; }
    } else {
      display.textContent = '—';
      summary.textContent = '—';
      label.className = 'rating-badge bg-secondary text-white';
      label.textContent = 'Not yet rated';
      label.style.background = '';
    }
  }

  function openEvidenceModalFor(categoryKey, mfoText) {
    const filtered = getMatchingEvidence(categoryKey, mfoText);
    const label = (mfoText ? mfoText + ' (' + (categoryKey||'').toUpperCase() + ')' : (categoryKey||'').toUpperCase() + ' Evidence');
    renderEvidenceList(filtered, label);
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (session.name || 'Campus Executive Officer');
    _evidenceModal.show();
  }

  function openEvidenceModal() {
    renderEvidenceList(currentEvidence, 'All Evidence');
    _evidenceModal = _evidenceModal || new bootstrap.Modal(document.getElementById('evidenceModal'));
    document.getElementById('evidenceModalUser').textContent = (session.name || 'Campus Executive Officer');
    _evidenceModal.show();
  }

  function renderEvidenceList(files, filterLabel) {
    document.getElementById('evidenceFilterBadge').textContent = filterLabel;
    const tbody = document.getElementById('evidenceModalTable');
    tbody.innerHTML = '';

    if (!files || files.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa-solid fa-folder-open me-2"></i>No evidence documents found for ${filterLabel}.</td></tr>`;
      return;
    }

    files.forEach((f, i) => {
      const name = f.original_name || f.name || 'document';
      const ext = name.split('.').pop().toLowerCase();
      const iconMap = { pdf: 'fa-file-pdf text-danger', doc: 'fa-file-word text-primary', docx: 'fa-file-word text-primary', jpg: 'fa-file-image text-success', jpeg: 'fa-file-image text-success', png: 'fa-file-image text-success', xlsx: 'fa-file-excel text-success' };
      const icon = iconMap[ext] || 'fa-file text-secondary';
      const sizeStr = (f.file_size || f.size) ? formatSize(f.file_size || f.size) : '-';
      let filePath = '';
      if (f.file_path && f.file_path !== '#') {
        filePath = f.file_path.startsWith('http') ? f.file_path : (API_BASE + '../' + f.file_path.replace(/^\/+/, ''));
      } else if (f.data_url) {
        filePath = f.data_url;
      } else if (f.file_url) {
        filePath = f.file_url;
      }

      const actionHtml = filePath
        ? `<a href="${filePath}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye me-1"></i>View / Open</a>`
        : `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="showToast('Physical file not saved on server yet. Please upload supporting evidence in the form.', 'warning')"><i class="fa-solid fa-file me-1"></i>Document Details</button>`;

      tbody.innerHTML += `<tr>
        <td>${i + 1}</td>
        <td><div class="d-flex align-items-center gap-2"><i class="fa-solid ${icon}" style="font-size:1.1rem"></i><strong>${esc(name)}</strong></div></td>
        <td>${getCategoryBadge(f.category || 'Evidence')}</td>
        <td style="font-size:0.82rem">${esc(f.description || '-')}</td>
        <td style="font-size:0.82rem">${sizeStr}</td>
        <td>${actionHtml}</td>
      </tr>`;
    });
  }

  function formatSize(bytes) {
    if (!bytes) return '-';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  let _uploadModal = null;

  function openUploadModal() {
    openUploadModalFor('core', '');
  }

  function openUploadModalFor(categoryKey = 'core', mfoText = '') {
    _uploadModal = _uploadModal || new bootstrap.Modal(document.getElementById('uploadEvidenceModal'));
    const form = document.getElementById('quickUploadForm');
    if (form) form.dataset.mfo = mfoText || '';
    const catSelect = document.getElementById('quickUploadCategory');
    if (catSelect) {
      const normCat = (categoryKey || '').toLowerCase();
      if (normCat.includes('core')) catSelect.value = 'core';
      else if (normCat.includes('strat')) catSelect.value = 'strategic';
      else if (normCat.includes('supp')) catSelect.value = 'support';
      else catSelect.value = 'other';
    }
    const descInput = document.getElementById('quickUploadDesc');
    if (descInput) descInput.value = mfoText || '';
    const fileInput = document.getElementById('quickUploadFiles');
    if (fileInput) fileInput.value = '';
    const st = document.getElementById('quickUploadStatus');
    if (st) st.classList.add('d-none');
    _uploadModal.show();
  }

  function openUploadModalFromEvidence() {
    if (_evidenceModal) _evidenceModal.hide();
    openUploadModal();
  }

  function updateAllRowEvidenceBtns() {
    document.querySelectorAll('tr[data-category]').forEach(tr => {
      const mfoInp = tr.querySelector('.mfo-input');
      if (mfoInp) updateRowEvidenceBtn(mfoInp);
    });
    const evCount = currentEvidence.length;
    const b1 = document.getElementById('evidenceCountBadge');
    const b2 = document.getElementById('evidenceCountBadge2');
    if (b1) b1.textContent = evCount;
    if (b2) b2.textContent = evCount;
  }

  async function submitQuickUpload(e) {
    e.preventDefault();
    const filesInput = document.getElementById('quickUploadFiles');
    const fileList = filesInput?.files;
    if (!fileList || fileList.length === 0) {
      showToast('Please select at least one file to upload.', 'warning');
      return;
    }

    const category = document.getElementById('quickUploadCategory').value;
    const desc = document.getElementById('quickUploadDesc').value.trim();
    const mfo = document.getElementById('quickUploadForm')?.dataset?.mfo || desc;
    const btn = document.getElementById('btnSubmitQuickUpload');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';

    const formData = new FormData();
    formData.append('category', category);
    formData.append('description', desc);
    formData.append('mfo', mfo);
    formData.append('user_id', session.id);
    if (existingOpcrId) formData.append('opcr_form_id', existingOpcrId);

    Array.from(fileList).forEach(f => formData.append('files[]', f));

    try {
      const res = await fetch(API_BASE + 'evidence/upload.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success && data.files) {
        data.files.forEach(nf => {
          currentEvidence.unshift(nf);
        });
        showToast(data.message || 'Evidence uploaded successfully!', 'success');
        updateAllRowEvidenceBtns();
        _uploadModal.hide();
      } else {
        showToast(data.error || 'Upload error.', 'danger');
      }
    } catch (err) {
      showToast('Upload failed. Please check your connection or file size.', 'danger');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload & Attach';
    }
  }

  async function initForm() {
    // Clean up any legacy localStorage ghost evidence
    if (session.id) {
      try { localStorage.removeItem('csu_piat_files_' + session.id); } catch(e) {}
    }

    const [tlRes, evRes] = await Promise.all([
      fetch(API_BASE + 'timeline/list.php?status=open', { credentials: 'include' }).then(r => r.json()).catch(() => null),
      session.id ? fetch(API_BASE + 'evidence/list.php?user_id=' + session.id, { credentials: 'include' }).then(r => r.json()).catch(() => null) : Promise.resolve(null),
    ]);
    activeTimeline = (tlRes?.timelines || [])[0] || null;

    // Real server evidence files
    let userFiles = (evRes && evRes.files) ? [...evRes.files] : [];

    // Check saved local storage or backend
    const savedLocal = session.id ? JSON.parse(localStorage.getItem(STORAGE_KEY + '_' + session.id) || 'null') : null;

    if (activeTimeline) {
      const deadline = new Date(activeTimeline.submission_deadline);
      const daysLeft = Math.ceil((deadline - new Date()) / 86400000);
      showToast(daysLeft > 0 ? `Active Period: ${activeTimeline.academic_year} (${activeTimeline.semester})` : 'Target accomplishment date has passed.', 'info');

      // Try loading backend OPCR form
      const existRes = await fetch(API_BASE + 'opcr/get.php?timeline_id=' + activeTimeline.id, { credentials: 'include' }).then(r => r.json()).catch(() => null);
      if (existRes?.form) {
        const f = existRes.form;
        existingOpcrId = f.id;
        document.getElementById('opcrPeriod').value = f.covered_period || '';
        document.getElementById('opcrOffice').value = f.department_name || 'Office of the Campus Executive Officer — CSU Piat';
        document.getElementById('lastSaved').textContent = f.updated_at ? new Date(f.updated_at).toLocaleString('en-PH') : 'Saved';
        setStatus(f.status || 'draft');

        if (f.etl_type) {
          selectEtl(f.etl_type, {
            core: f.weight_core,
            strategic: f.weight_strategic,
            support: f.weight_support
          });
        } else {
          selectEtl('0 ETL');
        }

        // Merge backend evidence files
        (f.evidence_files || []).forEach(bf => {
          if (!userFiles.some(uf => uf.original_name === bf.original_name || uf.name === bf.original_name)) {
            userFiles.push(bf);
          }
        });
        currentEvidence = userFiles;

        loadRows('coreBody', f.items?.core?.length ? f.items.core : DEFAULT_CORE);
        loadRows('strategicBody', f.items?.strategic?.length ? f.items.strategic : DEFAULT_STRATEGIC);
        loadRows('supportBody', f.items?.support?.length ? f.items.support : DEFAULT_SUPPORT);

        if (['pending', 'reviewed', 'approved'].includes(f.status)) {
          setReadOnly(true);
        }
      } else if (savedLocal) {
        currentEvidence = userFiles;
        document.getElementById('opcrPeriod').value = savedLocal.coveredPeriod || (activeTimeline.semester + ' ' + activeTimeline.academic_year);
        document.getElementById('opcrDate').value = savedLocal.date || new Date().toISOString().split('T')[0];
        document.getElementById('opcrSemester').value = savedLocal.semester || 'January to June';
        if (savedLocal.etlType) {
          selectEtl(savedLocal.etlType, {
            core: savedLocal.weightCore,
            strategic: savedLocal.weightStrategic,
            support: savedLocal.weightSupport
          });
        } else {
          selectEtl('0 ETL');
        }
        loadRows('coreBody', savedLocal.coreFunction || DEFAULT_CORE);
        loadRows('strategicBody', savedLocal.strategicFunction || DEFAULT_STRATEGIC);
        loadRows('supportBody', savedLocal.supportFunction || DEFAULT_SUPPORT);
        setStatus(savedLocal.status || 'draft');
        document.getElementById('lastSaved').textContent = savedLocal.savedAt ? new Date(savedLocal.savedAt).toLocaleString('en-PH') : 'Not yet saved';
        if (savedLocal.status === 'approved' || savedLocal.status === 'confirmed') setReadOnly(true);
      } else {
        currentEvidence = userFiles;
        document.getElementById('opcrPeriod').value = activeTimeline.semester + ' ' + activeTimeline.academic_year;
        selectEtl('0 ETL');
        setStatus('draft');
        loadRows('coreBody', DEFAULT_CORE);
        loadRows('strategicBody', DEFAULT_STRATEGIC);
        loadRows('supportBody', DEFAULT_SUPPORT);
      }
    } else {
      currentEvidence = userFiles;
      document.getElementById('noTimelineAlert').classList.remove('d-none');
      if (savedLocal) {
        document.getElementById('opcrPeriod').value = savedLocal.coveredPeriod || '';
        document.getElementById('opcrDate').value = savedLocal.date || new Date().toISOString().split('T')[0];
        document.getElementById('opcrSemester').value = savedLocal.semester || 'January to June';
        if (savedLocal.etlType) {
          selectEtl(savedLocal.etlType, {
            core: savedLocal.weightCore,
            strategic: savedLocal.weightStrategic,
            support: savedLocal.weightSupport
          });
        } else {
          selectEtl('0 ETL');
        }
        loadRows('coreBody', savedLocal.coreFunction || DEFAULT_CORE);
        loadRows('strategicBody', savedLocal.strategicFunction || DEFAULT_STRATEGIC);
        loadRows('supportBody', savedLocal.supportFunction || DEFAULT_SUPPORT);
        setStatus(savedLocal.status || 'draft');
      } else {
        selectEtl('0 ETL');
        setStatus('draft');
        loadRows('coreBody', DEFAULT_CORE);
        loadRows('strategicBody', DEFAULT_STRATEGIC);
        loadRows('supportBody', DEFAULT_SUPPORT);
      }
    }

    const evCount = currentEvidence.length;
    const b1 = document.getElementById('evidenceCountBadge');
    const b2 = document.getElementById('evidenceCountBadge2');
    if (b1) b1.textContent = evCount;
    if (b2) b2.textContent = evCount;

    updatePeriodSummary();
    computeAverages();
  }

  initForm();

  async function saveOPCR(action = 'draft') {
    const period = document.getElementById('opcrPeriod').value.trim();
    if (!period) { showToast('Please enter the rating period.', 'warning'); return false; }

    const coreRows = getRows('coreBody');
    if (coreRows.length === 0) { showToast('At least one Core Function row is required.', 'warning'); return false; }

    const strategicRows = getRows('strategicBody');
    const supportRows   = getRows('supportBody');
    const overallVal    = parseFloat(document.getElementById('finalAvgDisplay').textContent) || 0;

    // Save to LocalStorage as safety backup
    const localData = {
      id: existingOpcrId || 'sa-opcr-' + Date.now(),
      adminId: session.id,
      adminName: session.name,
      office: document.getElementById('opcrOffice').value,
      position: document.getElementById('opcrPosition').value,
      coveredPeriod: period,
      date: document.getElementById('opcrDate').value,
      semester: document.getElementById('opcrSemester').value,
      etlType: currentEtl,
      weightCore: activeWeights.core,
      weightStrategic: activeWeights.strategic,
      weightSupport: activeWeights.support,
      status: action === 'submit' ? 'approved' : 'draft',
      overallRating: overallVal,
      coreFunction: coreRows,
      strategicFunction: strategicRows,
      supportFunction: supportRows,
      savedAt: new Date().toISOString()
    };
    if (session.id) {
      localStorage.setItem(STORAGE_KEY + '_' + session.id, JSON.stringify(localData));
    }

    if (activeTimeline) {
      const payload = {
        action,
        opcr_id: existingOpcrId || 0,
        timeline_id: activeTimeline.id,
        covered_period: period,
        etl_type: currentEtl,
        weight_core: activeWeights.core,
        weight_strategic: activeWeights.strategic,
        weight_support: activeWeights.support,
        core:      coreRows,
        strategic: strategicRows,
        support:   supportRows,
      };

      try {
        const res = await fetch(API_BASE + 'opcr/save.php', {
          method: 'POST', credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        }).then(r => r.json()).catch(() => null);

        if (res?.success) {
          existingOpcrId = res.opcr_id;
          setStatus(res.status);
          document.getElementById('lastSaved').textContent = new Date().toLocaleString('en-PH');
          if (action === 'submit') {
            setReadOnly(true);
          }
          showToast(action === 'submit' ? 'OPCR confirmed and submitted successfully!' : 'Draft saved successfully!', 'success');
          return true;
        } else {
          showToast(res?.error || 'Failed to save OPCR to server, saved locally.', 'warning');
          setStatus(localData.status);
          document.getElementById('lastSaved').textContent = new Date().toLocaleString('en-PH');
          if (action === 'submit') setReadOnly(true);
          return true;
        }
      } catch {
        showToast('Saved locally. Server error.', 'info');
        setStatus(localData.status);
        document.getElementById('lastSaved').textContent = new Date().toLocaleString('en-PH');
        if (action === 'submit') setReadOnly(true);
        return true;
      }
    } else {
      setStatus(localData.status);
      document.getElementById('lastSaved').textContent = new Date().toLocaleString('en-PH');
      if (action === 'submit') setReadOnly(true);
      showToast(action === 'submit' ? 'OPCR confirmed & saved!' : 'Draft saved locally!', 'success');
      return true;
    }
  }

  function submitOPCR() {
    confirmModal('Confirm and submit your Institutional OPCR for the rating period?', 'Confirm OPCR', async () => {
      const ok = await saveOPCR('submit');
      if (ok) {
        showToast('Institutional OPCR saved and confirmed.', 'success');
      }
    });
  }

  // Preload logo for print preview
  let _printLogo = '';
  fetch('../../assets/images/csu-logo.png')
    .then(r => r.blob()).then(b => { const rd = new FileReader(); rd.onload = ev => { _printLogo = ev.target.result; }; rd.readAsDataURL(b); }).catch(() => {});

  function showPrintPreview() {
    const name   = document.getElementById('opcrName').value.trim();
    const pos    = document.getElementById('opcrPosition').value.trim();
    const office = document.getElementById('opcrOffice').value.trim();
    const period = document.getElementById('opcrPeriod').value.trim();
    const date   = document.getElementById('opcrDate').value;
    const sem    = document.getElementById('opcrSemester').value;

    if (!period) { showToast('Please enter the rating period before previewing.', 'warning'); return; }

    function ep(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function getFormRows(tbodyId) {
      const rows = [];
      document.getElementById(tbodyId).querySelectorAll('tr').forEach(tr => {
        const mfoInp     = tr.querySelector('.mfo-input');
        const siInp      = tr.querySelector('.si-input');
        const targetInp  = tr.querySelector('.target-input');
        const budgetInp  = tr.querySelector('.budget-input');
        const accInp     = tr.querySelector('.acc-input');
        const qInp       = tr.querySelector('.rating-q');
        const eInp       = tr.querySelector('.rating-e');
        const tInp       = tr.querySelector('.rating-t');
        const avgCell    = tr.querySelector('.row-avg');
        const remarksInp = tr.querySelector('.row-remarks');
        rows.push({
          mfo:     mfoInp?.value.trim() || '',
          si:      siInp?.value.trim() || '',
          target:  targetInp?.value.trim() || '',
          budget:  budgetInp?.value || '0',
          measure: '',
          actual:  accInp?.value.trim() || '',
          q:       qInp?.value || '',
          e:       eInp?.value || '',
          t:       tInp?.value || '',
          a:       avgCell?.textContent !== '-' ? avgCell?.textContent : '',
          remarks: remarksInp?.value || ''
        });
      });
      return rows;
    }

    const core      = getFormRows('coreBody');
    const strategic = getFormRows('strategicBody');
    const support   = getFormRows('supportBody');

    const etlName = currentEtl || '0 ETL';
    const wCore   = (activeWeights.core || 70) / 100;
    const wStrat  = (activeWeights.strategic || 15) / 100;
    const wSupp   = (activeWeights.support || 15) / 100;

    const cAvgs = core.map(r => parseFloat(r.a)).filter(v => v > 0);
    const coreAvg = cAvgs.length ? (cAvgs.reduce((a,b) => a+b, 0) / cAvgs.length) : null;

    const sAvgs = strategic.map(r => parseFloat(r.a)).filter(v => v > 0);
    const stratAvg = sAvgs.length ? (sAvgs.reduce((a,b) => a+b, 0) / sAvgs.length) : null;

    const supAvgs = support.map(r => parseFloat(r.a)).filter(v => v > 0);
    const suppAvg = supAvgs.length ? (supAvgs.reduce((a,b) => a+b, 0) / supAvgs.length) : null;

    const coreWeighted = coreAvg !== null ? (coreAvg * wCore) : null;
    const stratWeighted = stratAvg !== null ? (stratAvg * wStrat) : null;
    const suppWeighted = suppAvg !== null ? (suppAvg * wSupp) : null;

    let sum = 0, weightSum = 0;
    if (coreWeighted !== null) { sum += coreWeighted; weightSum += wCore; }
    if (stratWeighted !== null) { sum += stratWeighted; weightSum += wStrat; }
    if (suppWeighted !== null) { sum += suppWeighted; weightSum += wSupp; }

    const finalAvg = weightSum > 0 ? parseFloat((sum / weightSum).toFixed(2)) : 0;

    function adj(avg) {
      if (avg >= 4.5) return 'Outstanding';
      if (avg >= 3.5) return 'Very Satisfactory';
      if (avg >= 2.5) return 'Satisfactory';
      if (avg >= 1.5) return 'Unsatisfactory';
      if (avg > 0)    return 'Poor';
      return '';
    }

    function buildRows(rows, minRows) {
      let html = '';
      const total = Math.max(rows.length, minRows);
      for (let i = 0; i < total; i++) {
        const r = rows[i] || {};
        let formattedActual = '';
        if (r.actual !== undefined && r.actual !== null && String(r.actual).trim() !== '') {
          const actStr = String(r.actual).trim();
          if (actStr.includes('%')) {
            formattedActual = actStr;
          } else if (/%/.test(String(r.target || '')) && !isNaN(actStr)) {
            formattedActual = actStr + '%';
          } else {
            formattedActual = actStr;
          }
        }
        html += `<tr class="data-row">
          <td>${ep(r.mfo)}</td>
          <td>${ep(r.si)}</td>
          <td class="tc">${ep(r.target)}</td>
          <td class="tc">${ep(r.budget)}</td>
          <td class="tc">${ep(formattedActual)}</td>
          <td class="tc">${ep(r.q)}</td>
          <td class="tc">${ep(r.e)}</td>
          <td class="tc">${ep(r.t)}</td>
          <td class="tc b">${ep(r.a)}</td>
          <td>${ep(r.remarks)}</td>
        </tr>`;
      }
      return html;
    }

    const logoTag = _printLogo ? `<img src="${_printLogo}" class="logo" alt="CSU Logo">` : `<div class="logo-ph"></div>`;
    const html = `<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>OPCR — ${ep(name)}</title><style>
*{margin:0;padding:0;box-sizing:border-box;}body{font-family:'Times New Roman',Times,serif;font-size:7.8pt;color:#000;background:#fff;}
@page{size:letter landscape;margin:.35in .3in;}@media print{.no-print{display:none!important;}}
.no-print{position:fixed;top:10px;right:14px;z-index:999;display:flex;gap:8px;}
.no-print button{padding:7px 16px;font-size:12px;border:none;border-radius:4px;cursor:pointer;font-family:sans-serif;font-weight:600;}
.btn-pdf{background:#821131;color:#fff;}.btn-cls{background:#555;color:#fff;}
.form-outer{border:1.5px solid #000;width:100%;}table{width:100%;border-collapse:collapse;}
td,th{border:1px solid #000;padding:1.5px 3px;vertical-align:middle;font-size:7.8pt;}.tc{text-align:center;}.b{font-weight:700;}
.hdr-row{padding:5px 8px 4px;position:relative;border-bottom:1px solid #000;}
.annex{position:absolute;top:5px;right:8px;font-size:7.5pt;}
.hdr-inner{display:flex;align-items:center;justify-content:center;gap:8px;}
.logo{width:46px;height:46px;object-fit:contain;}.logo-ph{width:46px;height:46px;background:#ddd;border-radius:50%;}
.univ-text{text-align:center;line-height:1.5;}.univ-text .republic{font-size:7.5pt;}.univ-text .univ{font-size:9.5pt;font-weight:700;}.univ-text .campus{font-size:7.5pt;}
.form-title{text-align:center;font-weight:700;font-size:9pt;text-decoration:underline;margin-top:4px;padding-bottom:2px;}
.div-field{text-align:center;padding:3px 0 2px;border-bottom:1px solid #000;}
.uline{display:inline-block;border-bottom:1px solid #000;min-width:180px;font-size:7.8pt;}.field-lbl{font-size:6.5pt;display:block;margin-top:1px;}
.commit-wrap{display:table;width:100%;border-bottom:1px solid #000;}
.commit-left{display:table-cell;width:73%;padding:4px 8px;vertical-align:top;line-height:1.7;font-size:7.8pt;}
.commit-right{display:table-cell;width:27%;padding:4px 8px;vertical-align:bottom;border-left:1px solid #000;text-align:center;}
.sig-line{display:block;border-top:1px solid #000;margin:28px auto 1px;width:80%;font-size:7pt;}.date-line{font-size:7.5pt;margin-top:4px;}
.rev-table th{background:#fff;font-weight:700;font-size:7.5pt;text-align:center;padding:2px 4px;}
.rev-table td{font-size:7.5pt;padding:3px 5px;vertical-align:bottom;}.rev-name{font-weight:700;font-size:7.8pt;}.rev-role{font-size:6.5pt;font-style:italic;}
.data-table{border-top:1px solid #000;}.data-table th{background:#d9d9d9;font-weight:700;text-align:center;font-size:7.3pt;padding:2px 3px;}
.data-table .sec-row td{background:#fed7aa;font-weight:700;font-size:7.8pt;text-align:left;padding:2px 5px;}
.data-table .data-row td{height:18px;font-size:7.5pt;padding:1px 3px;vertical-align:top;}
.summary-table td{border:1px solid #000;padding:1.5px 5px;font-size:7.5pt;}.summary-table .lbl{font-weight:700;}.summary-table .val{text-align:center;font-weight:700;}
.sig-tbl th{background:#fff;font-weight:700;text-align:center;font-size:7.3pt;border:1px solid #000;padding:2px 4px;}
.sig-tbl td{border:1px solid #000;padding:2px 4px;font-size:7.3pt;vertical-align:top;}
.sig-tbl .certify{font-style:italic;font-size:7pt;text-align:center;}.sig-tbl .sig-name-cell{font-weight:700;text-align:center;}
.legend-wrap{display:table;width:100%;border-top:none;}
.legend-blank{display:table-cell;width:38%;border-right:1px solid #000;}
.legend-right{display:table-cell;width:62%;}
.legend-right table{border:none;}
.legend-right td{border:none;border-bottom:1px solid #ccc;font-size:7.3pt;padding:1px 3px;}
.legend-right td:first-child{font-weight:700;text-align:center;border-right:1px solid #000;width:20px;border-left:1px solid #000;}
.legend-right tr:first-child td{border-top:1px solid #000;}
.legend-right tr:last-child td{border-bottom:1px solid #000;}
.legend-note{font-size:6.5pt;padding:2px 5px;font-style:italic;}
</style></head><body>
<div class="no-print"><button class="btn-pdf" onclick="window.print()">&#128438; Print / Save as PDF</button><button class="btn-cls" onclick="window.close()">&#x2715; Close</button></div>
<div class="form-outer">
<div class="hdr-row"><div class="annex">ANNEX B</div><div class="hdr-inner">${logoTag}<div class="univ-text"><div class="republic">Republic of the Philippines</div><div class="univ">CAGAYAN STATE UNIVERSITY</div><div class="campus">Piat Campus, Piat, Cagayan</div></div></div><div class="form-title">OFFICE PERFORMANCE COMMITMENT AND REVIEW FORM (OPCR) — INSTITUTIONAL</div></div>
<div class="div-field"><span class="uline">&nbsp;${ep(office)}&nbsp;</span><span class="field-lbl">Division/Office — Rating Period: ${ep(sem)}</span></div>
<div class="commit-wrap"><div class="commit-left">I,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(name)}</span>,&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(pos)}</span>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measures for<br>the period&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(period)}</span>.</div><div class="commit-right"><span class="sig-line">${ep(name)}<br><span style="font-size:6.5pt;font-style:italic">(Campus Executive Officer)</span></span><div class="date-line">Date:&nbsp;<span style="border-bottom:1px solid #000;padding:0 4px">${ep(date)}</span></div></div></div>
<table class="rev-table"><tr><th style="width:45%">REVIEWED / APPROVED BY</th><th style="width:10%">DATE</th><th style="width:35%">NOTED BY</th><th style="width:10%">DATE</th></tr>
<tr><td style="height:32px;vertical-align:bottom;text-align:center"><div class="rev-name">VP FOR ACADEMIC AFFAIRS</div><div class="rev-role">(University System Rater)</div></td><td>&nbsp;</td><td style="text-align:center;vertical-align:middle"><div class="rev-name">University President</div><div class="rev-role">CSU System Administration</div></td><td>&nbsp;</td></tr></table>
<div class="legend-wrap" style="border-top:1px solid #000;">
  <div class="legend-blank">&nbsp;</div>
  <div class="legend-right">
    <table>
      <tr><td>R</td><td>5 – Outstanding &nbsp;- performance exceeded expectation by 30% and above of planned target</td></tr>
      <tr><td>A</td><td>4 – Very Satisfactory &nbsp;- performance exceeded expectations by 15% to 29% of planned targets</td></tr>
      <tr><td>T</td><td>3 – Satisfactory &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- performance met 90% to 114% of the planned targets</td></tr>
      <tr><td>I</td><td>2 – Unsatisfactory &nbsp;&nbsp;- performance only met 51% to 89% of planned targets and failed to deliver one or</td></tr>
      <tr><td>N</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;more critical aspects of the targets</td></tr>
      <tr><td>G</td><td>1 – Poor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- performance failed to deliver most of the targets by 50% and below</td></tr>
    </table>
  </div>
</div>
<table class="data-table"><colgroup><col style="width:18%"><col style="width:20%"><col style="width:8%"><col style="width:9%"><col style="width:15%"><col style="width:4%"><col style="width:4%"><col style="width:4%"><col style="width:4%"><col style="width:14%"></colgroup>
<thead><tr><th rowspan="2">MFO/PAP</th><th colspan="2" rowspan="2">SUCCESS INDICATORS<br>(TARGET + MEASURES)</th><th rowspan="2">BUDGET (₱)</th><th rowspan="2">ACTUAL ACCOMPLISHMENTS</th><th colspan="4">RATING</th><th rowspan="2">REMARKS</th></tr><tr><th>Q<sup>1</sup></th><th>E<sup>2</sup></th><th>T<sup>3</sup></th><th>A<sup>4</sup></th></tr></thead>
<tbody>
<tr class="sec-row"><td colspan="10">A. CORE FUNCTIONS</td></tr>
${buildRows(core,5)}
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
  <td class="tc b">${coreAvg !== null ? coreAvg.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
  <td class="tc b">${coreWeighted !== null ? coreWeighted.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
<tr class="sec-row"><td colspan="10">B. STRATEGIC FUNCTIONS</td></tr>
${buildRows(strategic,3)}
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
  <td class="tc b">${stratAvg !== null ? stratAvg.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
  <td class="tc b">${stratWeighted !== null ? stratWeighted.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
<tr class="sec-row"><td colspan="10">C. SUPPORT FUNCTIONS</td></tr>
${buildRows(support,3)}
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Average Rating</td>
  <td class="tc b">${suppAvg !== null ? suppAvg.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
<tr class="data-row">
  <td colspan="8" style="font-weight:700;text-align:left;padding-left:8px;">Weighted Average Rating</td>
  <td class="tc b">${suppWeighted !== null ? suppWeighted.toFixed(2) : ''}</td>
  <td>&nbsp;</td>
</tr>
</tbody></table>
<table class="summary-table"><tr><td class="lbl" style="width:20%">AVERAGE RATING:</td><td class="val">${finalAvg||''}</td></tr><tr><td class="lbl">FINAL AVERAGE RATING:</td><td class="val">${finalAvg||''}</td></tr><tr><td class="lbl">ADJECTIVAL RATING:</td><td class="val">${finalAvg?adj(finalAvg):''}</td></tr></table>
<table class="sig-tbl"><tr><th style="width:30%">CAMPUS EXECUTIVE OFFICER</th><th style="width:10%">DATE</th><th style="width:30%">REVIEWED / APPROVED BY</th><th style="width:10%">DATE</th><th style="width:10%">FINAL RATING</th><th style="width:10%">DATE</th></tr>
<tr style="height:52px">
  <td>&nbsp;</td><td>&nbsp;</td>
  <td class="certify">VP for Academic Affairs / University President</td><td>&nbsp;</td>
  <td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr>
  <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
    <div class="rev-name">${ep(name)}</div>
    <div class="rev-role" style="font-style:normal;text-transform:uppercase">${ep(pos || 'CAMPUS EXECUTIVE OFFICER')}</div>
  </td>
  <td>&nbsp;</td>
  <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
    <div class="rev-name">VP FOR ACADEMIC AFFAIRS</div>
    <div class="rev-role" style="font-style:normal;text-transform:uppercase">UNIVERSITY SYSTEM RATER</div>
  </td>
  <td>&nbsp;</td>
  <td class="sig-name-cell" style="border-top:1px solid #aaa;text-align:center">
    <div class="rev-name">${finalAvg ? finalAvg.toFixed(2) : ''}</div>
    <div class="rev-role">${finalAvg ? adj(finalAvg) : ''}</div>
  </td>
  <td>&nbsp;</td>
</tr>
<tr><td colspan="6" class="legend-note">Legend: 1:Quality &nbsp; 2:Efficiency &nbsp; 3:Timeliness &nbsp; 4:Average</td></tr></table>
</div><script>setTimeout(()=>window.print(),700);<\/script></body></html>`;

    const w = window.open('', '_blank');
    if (!w) { showToast('Please allow popups for this site to use Print Preview.', 'warning'); return; }
    w.document.write(html); w.document.close();
  }
</script>
</body>
</html>
