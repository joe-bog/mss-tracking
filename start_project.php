<?php
include 'auth_check.php';
include 'db.php';

// Load all customers
$customers = $conn->query("
    SELECT customer_code, customer_name
    FROM customers
    ORDER BY customer_name ASC
");

// Load all templates (we will filter client-side)
$templates = $conn->query("
    SELECT template_id, template_name, customer_code
    FROM project_templates
    ORDER BY template_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Project</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header .user-info {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-section-title {
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-group select {
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }
        
        .form-group select:disabled {
            background-color: #f8f9fa;
            color: #bdc3c7;
            cursor: not-allowed;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23bdc3c7' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input:hover:not(:disabled),
        .form-group select:hover:not(:disabled) {
            border-color: #b8c2cc;
        }
        
        #dynamic_fields {
            margin-top: 20px;
        }
        
        #dynamic_fields .form-group {
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.2s;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .back-link:hover {
            background: #f8f9fa;
            transform: translateX(-4px);
        }
        
        .helper-text {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 6px;
        }
        
        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        
        .loading-indicator.show {
            display: block;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e0e6ed;
            z-index: 0;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e6ed;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background: #667eea;
            color: white;
        }
        
        .step.completed .step-circle {
            background: #27ae60;
            color: white;
        }
        
        .step-label {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 600;
        }
        
        .step.active .step-label {
            color: #667eea;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                max-width: 100%;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .card {
                padding: 20px;
            }
            
            .step-indicator {
                padding: 15px;
            }
            
            .step-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Start New Project</h1>
            <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
        
        <div class="card">
            <div class="step-indicator">
                <div class="step" id="step1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Customer</div>
                </div>
                <div class="step" id="step2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Template</div>
                </div>
                <div class="step" id="step3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Details</div>
                </div>
                <div class="step" id="step4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Quantity of 12' Planks</div>
                </div>
            </div>
            
            <form method="POST" action="process_start_project.php">
                <div class="form-section">
                    <div class="form-section-title">👥 Step 1: Select Customer</div>
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="customer_code" id="customer_select" required onchange="filterTemplates()">
                            <option value="">-- Select Customer --</option>
                            <?php while ($c = $customers->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($c['customer_code']); ?>">
                                    <?= htmlspecialchars($c['customer_name']); ?>
                                    (<?= htmlspecialchars($c['customer_code']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <p class="helper-text">Choose the customer for this project</p>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">📋 Step 2: Select Template</div>
                    <div class="form-group">
                        <label>Project Template</label>
                        <select name="template_id" id="template_select" required onchange="loadFields(this.value)" disabled>
                            <option value="">-- Select Template --</option>
                            <?php while ($t = $templates->fetch_assoc()): ?>
                                <option value="<?= $t['template_id']; ?>"
                                        data-customer="<?= htmlspecialchars($t['customer_code']); ?>"
                                        style="display:none;">
                                    <?= htmlspecialchars($t['template_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <p class="helper-text">Select a customer first to see available templates</p>
                    </div>
                </div>
                
                <div class="form-section" id="details-section" style="display:none;">
                    <div class="form-section-title">📝 Step 3: Project Details</div>
                    <div id="dynamic_fields"></div>
                    <div class="loading-indicator" id="loading">
                        <div style="font-size: 32px; margin-bottom: 10px;">⏳</div>
                        Loading fields...
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-section-title">🔢 Step 4: Quantity</div>
                    <div class="form-group">
                        <label>Quantity Required</label>
                        <input type="number" name="requested_qty" min="1" placeholder="Enter quantity" required>
                        <p class="helper-text">How many 12ft planks are used?</p>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">✓ Start Project</button>
            </form>
        </div>
        
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <script>
    function updateStepIndicator(step) {
        for (let i = 1; i <= 4; i++) {
            const stepEl = document.getElementById('step' + i);
            stepEl.classList.remove('active', 'completed');
            if (i < step) {
                stepEl.classList.add('completed');
            } else if (i === step) {
                stepEl.classList.add('active');
            }
        }
    }

    // Initialize step 1 as active
    updateStepIndicator(1);

    function filterTemplates() {
        const customer = document.getElementById('customer_select').value;
        const templateSelect = document.getElementById('template_select');
        const options = templateSelect.querySelectorAll('option');

        // Reset template selection
        templateSelect.value = '';
        templateSelect.disabled = !customer;

        // Clear dynamic fields
        document.getElementById('dynamic_fields').innerHTML = '';
        document.getElementById('details-section').style.display = 'none';

        options.forEach(opt => {
            if (!opt.value) return; // Skip placeholder

            if (opt.dataset.customer === customer) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });

        // Update step indicator
        if (customer) {
            updateStepIndicator(2);
        } else {
            updateStepIndicator(1);
        }
    }

    function loadFields(templateId) {
        const detailsSection = document.getElementById('details-section');
        const dynamicFields = document.getElementById('dynamic_fields');
        const loading = document.getElementById('loading');
        
        if (!templateId) {
            dynamicFields.innerHTML = '';
            detailsSection.style.display = 'none';
            updateStepIndicator(2);
            return;
        }

        // Show loading
        detailsSection.style.display = 'block';
        dynamicFields.style.display = 'none';
        loading.classList.add('show');
        updateStepIndicator(3);

        const xhr = new XMLHttpRequest();
        xhr.open("GET", "load_fields.php?template_id=" + templateId, true);
        xhr.onload = function() {
            loading.classList.remove('show');
            dynamicFields.style.display = 'block';
            dynamicFields.innerHTML = this.responseText;
            updateStepIndicator(3);
        };
        xhr.send();
    }

    // Update step indicator when quantity field is focused
    document.addEventListener('DOMContentLoaded', function() {
        const qtyField = document.querySelector('input[name="requested_qty"]');
        if (qtyField) {
            qtyField.addEventListener('focus', function() {
                if (document.getElementById('template_select').value) {
                    updateStepIndicator(4);
                }
            });
        }
    });
    </script>
</body>
</html>