<?php
// Quick modal fix - this patches the issue with the bill creation modal

// Get shop ID from the URL if it exists
$shop_id = isset($_GET['shop']) ? intval($_GET['shop']) : 1;

// Output HTML
echo '<!DOCTYPE html>
<html>
<head>
    <title>Modal Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3498db;
        }
        .modal {
            margin-top: 30px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bill Creation Form</h1>
        <p>This is a direct form to create a new bill, bypassing the modal issue</p>
        
        <div class="modal">
            <h2>Create New Bill</h2>
            <form id="directBillForm" method="POST">
                <input type="hidden" id="shopId" name="shopId" value="' . $shop_id . '">
                
                <div class="form-group">
                    <label for="clientName">Client Name</label>
                    <input type="text" id="clientName" name="clientName" required>
                </div>
                
                <div class="form-group">
                    <label for="billAmount">Amount</label>
                    <input type="number" id="billAmount" name="billAmount" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="billDate">Date</label>
                    <input type="date" id="billDate" name="billDate" value="' . date('Y-m-d') . '" required>
                </div>
                
                <div class="form-group">
                    <label for="billStatus">Status</label>
                    <select id="billStatus" name="billStatus" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="billDescription">Description</label>
                    <textarea id="billDescription" name="billDescription" rows="4"></textarea>
                </div>
                
                <button type="submit" class="btn">Create Bill</button>
            </form>
        </div>
        
        <p style="margin-top: 20px;">
            <a href="bill.html?shop=' . $shop_id . '">Back to Bills</a>
        </p>
    </div>
    
    <script>
        // Handle form submission
        document.getElementById("directBillForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const shopId = document.getElementById("shopId").value;
            
            // Create form data object
            const formData = {
                client_name: document.getElementById("clientName").value,
                amount: document.getElementById("billAmount").value,
                date: document.getElementById("billDate").value,
                status: document.getElementById("billStatus").value,
                description: document.getElementById("billDescription").value
            };
            
            // Send form data to API
            fetch(`api/bills.php?shop=${shopId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Bill created successfully!");
                    window.location.href = `bill.html?shop=${shopId}`;
                } else {
                    alert("Error: " + (data.message || "Failed to create bill"));
                }
            })
            .catch(error => {
                console.error("Error creating bill:", error);
                alert("An error occurred. Please try again.");
            });
        });
    </script>
</body>
</html>';
?> 