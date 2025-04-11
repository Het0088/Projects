document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const billsTable = document.getElementById('billsTable');
    const billsList = document.getElementById('billsList');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const noBillsMessage = document.getElementById('noBillsMessage');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const newBillBtn = document.getElementById('newBillBtn');
    const billModal = document.getElementById('billModal');
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.getElementById('cancelBtn');
    const billForm = document.getElementById('billForm');
    const modalTitle = document.getElementById('modalTitle');
    const billIdField = document.getElementById('billId');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');
    
    // View Bill Modal Elements
    const viewBillModal = document.getElementById('viewBillModal');
    const closeViewBtn = document.querySelector('.close-view-btn');
    const printBillBtn = document.getElementById('printBillBtn');
    const viewBillId = document.getElementById('viewBillId');
    const viewBillDate = document.getElementById('viewBillDate');
    const viewBillStatus = document.getElementById('viewBillStatus');
    const viewClientName = document.getElementById('viewClientName');
    const viewBillDescription = document.getElementById('viewBillDescription');
    const viewBillAmount = document.getElementById('viewBillAmount');
    const closeViewBtnBottom = document.getElementById('closeViewBtn');
    const viewProductNumber = document.getElementById('viewProductNumber');

    // Get shop ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const shopId = urlParams.get('shop') || 1; // Default to shop 1 if not specified
    
    // Update page title with shop information
    document.title = `Shop ${shopId} - Billing Portal`;
    
    // Update header with shop information
    const headerTitle = document.querySelector('.logo h1');
    if (headerTitle) {
        headerTitle.textContent = `Employee Portal - Shop ${shopId} Billing`;
    }

    // State variables
    let currentTab = 'recent';
    let currentPage = 1;
    let totalPages = 1;
    let searchQuery = '';
    let editingBill = null;
    let currentShop = null;
    
    // Initialize
    loadBills();
    
    // Event Listeners
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.getAttribute('data-tab');
            switchToTab(tab);
        });
    });
    
    searchBtn.addEventListener('click', () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        loadBills();
    });
    
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            searchQuery = searchInput.value.trim();
            currentPage = 1;
            loadBills();
        }
    });
    
    newBillBtn.addEventListener('click', (e) => {
        // Prevent any event propagation
        e.preventDefault();
        e.stopPropagation();
        
        // Open the modal with a slight delay
        setTimeout(() => {
            openModal();
        }, 50);
    });
    
    // Remove existing event listeners and create new ones
    document.getElementById('saveBtn').onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Save button clicked directly');
        saveBill();
        return false;
    };

    // Set up direct click handler for the cancelBtn  
    document.getElementById('cancelBtn').onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Cancel button clicked directly');
        closeModal();
        return false;
    };

    // Set up direct click handler for the closeBtn
    document.querySelector('.close-btn').onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Close button clicked directly');
        closeModal();
        return false;
    };
    
    // Modal close when clicking outside
    window.addEventListener('click', (e) => {
        // Only close the modal if the click is directly on the modal background
        // and not on any of its children elements
        if (e.target === billModal) {
            closeModal();
        }
        
        // Same for view bill modal
        if (e.target === viewBillModal) {
            closeViewBillModal();
        }
    });
    
    // Prevent modal from closing when clicking inside
    billModal.querySelector('.modal-content').addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Only close modal when clicking outside
    billModal.addEventListener('click', function(e) {
        if (e.target === billModal) {
            closeModal();
        }
    });
    
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadBills();
        }
    });
    
    nextPageBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadBills();
        }
    });
    
    // Add event listeners for view bill modal
    closeViewBtn.addEventListener('click', closeViewBillModal);
    closeViewBtnBottom.addEventListener('click', closeViewBillModal);
    printBillBtn.addEventListener('click', printBill);
    
    // Functions
    function loadBills(forceRefresh = false) {
        showLoading();
        
        // Build query parameters
        const params = new URLSearchParams({
            page: currentPage,
            tab: currentTab,
            shop: shopId // Add shop ID to API requests
        });
        
        // Add cache busting parameter if force refresh requested
        if (forceRefresh) {
            params.append('_cache', new Date().getTime());
        }
        
        if (searchQuery) {
            params.append('search', searchQuery);
        }
        
        console.log(`Fetching bills with params: ${params.toString()}`);
        
        // Use relative path to make sure it works with XAMPP
        const apiUrl = `api/bills.php?${params.toString()}`;
        console.log('API URL:', apiUrl);
        
        // Set a timeout to prevent infinite loading state
        const loadingTimeout = setTimeout(() => {
            hideLoading();
            displayError(`
                <p>The server is taking too long to respond. Please try again and check:</p>
                <ol>
                    <li>MySQL server is running in XAMPP Control Panel</li>
                    <li>There are no network connectivity issues</li>
                </ol>
            `);
        }, 15000); // 15 seconds timeout
        
        // Fetch data from server with no-cache headers
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            cache: forceRefresh ? 'no-store' : 'default' // Force bypass cache when requested
        })
            .then(response => {
                console.log('Response status:', response.status);
                clearTimeout(loadingTimeout); // Clear timeout as we got a response
                
                if (!response.ok) {
                    if (response.status === 404) {
                        displayError('No bills found. Create a new bill to get started.');
                    } else {
                        displayError(`
                            <p>Server error (${response.status}). Please try the following:</p>
                            <ol>
                                <li>Make sure MySQL server is running in XAMPP Control Panel</li>
                                <li>Visit <a href="fix_database.php" target="_blank">Fix Database</a> to repair database issues</li>
                                <li>Visit <a href="db_test.php" target="_blank">Test Database</a> to check connection</li>
                            </ol>
                        `);
                    }
                    throw new Error(`Server response was not ok: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API response:', data);
                if (data.success) {
                    // Store shop information
                    currentShop = data.shop;
                    
                    // Update UI with shop information
                    updateShopInfo(currentShop);
                    
                    console.log('Displaying bills:', data.bills.length);
                    
                    // Display bills
                    displayBills(data.bills);
                    updatePagination(data.pagination);
                    
                    // Force a re-render to make sure changes take effect
                    setTimeout(() => {
                        // Also update statistics
                        updateBillStatistics();
                        console.log('Delayed statistics update triggered');
                    }, 500);
                } else {
                    if (data.debug_info) {
                        console.error('Debug info:', data.debug_info);
                    }
                    displayError(`
                        <p>${data.message || 'Failed to load bills'}</p>
                        <p>Please visit <a href="fix_database.php" target="_blank">Fix Database</a> to repair database issues.</p>
                    `);
                }
                hideLoading();
            })
            .catch(error => {
                clearTimeout(loadingTimeout); // Clear timeout as we got a response (error)
                console.error('Error fetching bills:', error);
                displayError(`
                    <p>Error connecting to server. Please check the following:</p>
                    <ol>
                        <li>Your internet connection is working</li>
                        <li>The server is running properly</li>
                        <li>Try refreshing the page</li>
                    </ol>
                    <p><a href="javascript:void(0)" onclick="loadBills()">Click here to try again</a></p>
                `);
                hideLoading();
            });
    }
    
    function displayBills(bills) {
        billsList.innerHTML = '';
        
        if (bills.length === 0) {
            billsTable.classList.add('hidden');
            noBillsMessage.classList.remove('hidden');
            return;
        }
        
        billsTable.classList.remove('hidden');
        noBillsMessage.classList.add('hidden');
        
        bills.forEach(bill => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>#${bill.id}</td>
                <td>${bill.client_name}</td>
                <td>$${parseFloat(bill.amount).toFixed(2)}</td>
                <td>${formatDate(bill.date)}</td>
                <td><span class="status-badge status-${bill.status}" data-id="${bill.id}" data-current="${bill.status}">${capitalizeFirst(bill.status)}</span></td>
                <td>
                    <i class="fas fa-eye action-icon view-icon" data-id="${bill.id}" title="View"></i>
                    <i class="fas fa-edit action-icon edit-icon" data-id="${bill.id}" title="Edit"></i>
                    <i class="fas fa-trash action-icon delete-icon" data-id="${bill.id}" title="Delete"></i>
                </td>
            `;
            
            billsList.appendChild(row);
        });
        
        // Add event listeners to action icons
        document.querySelectorAll('.edit-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const billId = icon.getAttribute('data-id');
                editBill(billId);
            });
        });
        
        document.querySelectorAll('.delete-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const billId = icon.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this bill?')) {
                    deleteBill(billId);
                }
            });
        });
        
        document.querySelectorAll('.view-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const billId = icon.getAttribute('data-id');
                viewBill(billId);
            });
        });
        
        // Add click handler for status badges to allow direct status updates
        document.querySelectorAll('.status-badge').forEach(badge => {
            badge.addEventListener('click', () => {
                const billId = badge.getAttribute('data-id');
                const currentStatus = badge.getAttribute('data-current');
                updateBillStatus(billId, currentStatus);
            });
        });
    }
    
    function updatePagination(pagination) {
        currentPage = pagination.current_page;
        totalPages = pagination.total_pages;
        
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
    }
    
    function editBill(billId) {
        // Fetch bill details with shop parameter
        const apiUrl = `api/bills.php?id=${billId}&shop=${shopId}`;
        console.log('Edit Bill API URL:', apiUrl);
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const bill = data.bill;
                    editingBill = bill;
                    
                    // Populate form
                    document.getElementById('billId').value = bill.id;
                    document.getElementById('clientName').value = bill.client_name;
                    document.getElementById('billAmount').value = bill.amount;
                    document.getElementById('billDate').value = bill.date;
                    document.getElementById('billStatus').value = bill.status;
                    document.getElementById('productNumber').value = bill.product_number || '';
                    document.getElementById('billDescription').value = bill.description;
                    
                    // Update modal title
                    modalTitle.textContent = `Edit Bill - ${currentShop?.name || 'Shop ' + shopId}`;
                    
                    // Open modal
                    openModal();
                } else {
                    alert('Could not retrieve bill details');
                }
            })
            .catch(error => {
                console.error('Error fetching bill details:', error);
                alert('An error occurred while fetching bill details');
            });
    }
    
    function viewBill(billId) {
        // Fetch bill details with shop parameter
        const apiUrl = `api/bills.php?id=${billId}&shop=${shopId}`;
        console.log('View Bill API URL:', apiUrl);
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBillDetails(data.bill);
                } else {
                    showNotification(data.message || 'Could not load bill details', 'error');
                }
            })
            .catch(error => {
                console.error('Error viewing bill:', error);
                showNotification('An error occurred', 'error');
            });
    }
    
    function displayBillDetails(bill) {
        // Update bill details in the view modal
        viewBillId.textContent = `Invoice #: ${bill.id}`;
        viewBillDate.textContent = `Date: ${formatDate(bill.date)}`;
        viewBillStatus.textContent = `Status: ${capitalizeFirst(bill.status)}`;
        viewProductNumber.textContent = `Product #: ${bill.product_number || 'N/A'}`;
        viewClientName.textContent = bill.client_name;
        viewBillDescription.textContent = bill.description || 'No description provided';
        viewBillAmount.textContent = `$${parseFloat(bill.amount).toFixed(2)}`;
        
        // Show the modal
        viewBillModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeViewBillModal() {
        viewBillModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    function printBill() {
        window.print();
    }
    
    function deleteBill(billId) {
        const apiUrl = `api/delete_bill.php?id=${billId}&_cache=${new Date().getTime()}`;
        console.log('Delete Bill API URL:', apiUrl);
        
        // Show a deleting notification
        showNotification('Deleting bill...', 'info');
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Delete response:', data);
            if (data.success) {
                showNotification('Bill deleted successfully', 'success');
                
                // Force refresh with a small delay to ensure server processing is complete
                setTimeout(() => {
                    console.log('Refreshing data after delete');
                    // Force refresh bills
                    loadBills();
                    
                    // Force refresh statistics with slight delay to ensure sequential processing
                    setTimeout(() => {
                        updateBillStatistics();
                    }, 300);
                }, 500);
            } else {
                showNotification(data.message || 'Failed to delete bill', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting bill:', error);
            showNotification('An error occurred while deleting the bill', 'error');
        });
    }
    
    function saveBill() {
        // Try to find input elements with different possible IDs
        const getInputValue = (possibleIds) => {
            for (const id of possibleIds) {
                const element = document.getElementById(id);
                if (element) return element.value;
            }
            return '';
        };
        
        const billId = getInputValue(['bill-id', 'billId']);
        const clientName = getInputValue(['client-name', 'clientName']);
        const amount = getInputValue(['amount', 'billAmount']);
        const date = getInputValue(['bill-date', 'billDate']);
        const productNumber = getInputValue(['product-number', 'productNumber']);
        const description = getInputValue(['description', 'billDescription']);
        const status = getInputValue(['status', 'billStatus']) || 'pending';
        const shopIdToUse = shopId || currentShopId || 1;
        
        console.log('Saving bill with ID:', billId, 'client:', clientName);
        
        const billData = {
            id: billId,
            client_name: clientName,
            amount: amount,
            date: date,
            product_number: productNumber,
            description: description,
            status: status,
            shop_id: shopIdToUse
        };
        
        console.log('Bill data to save:', billData);
        
        const url = 'api/save_bill.php';
        
        // Show a saving notification
        showNotification('Saving bill...', 'info');
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            body: JSON.stringify(billData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Save response:', data);
            if (data.success) {
                // Close modal if open
                if (typeof closeModal === 'function') {
                    closeModal();
                } else if (billModal) {
                    billModal.style.display = 'none';
                }
                
                showNotification(data.message, 'success');
                
                // Force refresh with a small delay to ensure server processing is complete
                setTimeout(() => {
                    console.log('Refreshing data after save');
                    // Force refresh bills
                    loadBills();
                    
                    // Force refresh statistics with slight delay to ensure sequential processing
                    setTimeout(() => {
                        updateBillStatistics();
                    }, 300);
                }, 500);
            } else {
                showNotification(data.message || 'Error saving bill', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving bill:', error);
            showNotification('Failed to save bill', 'error');
        });
    }
    
    function openModal() {
        console.log('Opening modal...');
        
        // Reset any previous form data
        resetForm();
        
        // Force display block first
        billModal.style.display = 'block';
        
        // Force a reflow before adding the active class
        void billModal.offsetWidth;
        
        // Add active class for animation
        billModal.classList.add('active');
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
        
        // Update modal title to include shop name
        if (!document.getElementById('billId').value) {
            modalTitle.textContent = `Create New Bill - ${currentShop?.name || 'Shop ' + shopId}`;
        }
        
        // Make sure button handlers are properly attached
        document.getElementById('saveBtn').onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Save button clicked from modal open');
            saveBill();
            return false;
        };
        
        document.getElementById('cancelBtn').onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Cancel button clicked from modal open');
            closeModal();
            return false;
        };
        
        document.querySelector('.close-btn').onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close button clicked from modal open');
            closeModal();
            return false;
        };
        
        // Also handle form submission directly
        document.getElementById('billForm').onsubmit = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Form submitted from modal open');
            saveBill();
            return false;
        };
        
        console.log('Modal should be visible now');
    }
    
    function closeModal() {
        // Remove active class first
        billModal.classList.remove('active');
        
        // Wait for animation to complete before hiding
        setTimeout(() => {
            billModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForm();
        }, 300); // Match this with CSS animation duration
    }
    
    function resetForm() {
        billForm.reset();
        billIdField.value = '';
        modalTitle.textContent = 'Create New Bill';
        editingBill = null;
    }
    
    function showLoading() {
        loadingSpinner.classList.remove('hidden');
        billsTable.classList.add('hidden');
        noBillsMessage.classList.add('hidden');
    }
    
    function hideLoading() {
        loadingSpinner.classList.add('hidden');
    }
    
    function displayError(message) {
        let errorMsg = message;
        
        // Add automatic fix button if it's a database error
        if (message.includes('database') || message.includes('server') || message.includes('MySQL')) {
            errorMsg += `
            <div style="margin-top: 20px; text-align: center;">
                <a href="fix_database.php" class="btn-primary" style="display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                    <i class="fas fa-magic"></i> Auto-Fix Database
                </a>
            </div>`;
        }
        
        noBillsMessage.innerHTML = errorMsg;
        noBillsMessage.classList.remove('hidden');
        billsTable.classList.add('hidden');
    }
    
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Add to document
        document.body.appendChild(notification);
        
        // Add styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 4px;
                color: white;
                font-weight: 500;
                z-index: 1100;
                animation: slideInRight 0.3s ease, fadeOut 0.5s ease 2.5s forwards;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            .notification.success {
                background-color: var(--secondary-color);
            }
            .notification.error {
                background-color: var(--danger-color);
            }
            .notification.info {
                background-color: var(--primary-color);
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes fadeOut {
                to { opacity: 0; visibility: hidden; }
            }
        `;
        document.head.appendChild(style);
        
        // Remove after animation
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Helper functions
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    function capitalizeFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Update our main page script to redirect to billing page
    function redirectToBillingPage() {
        window.location.href = 'bill.html';
    }

    // Error handling for database issues
    function handleApiErrors() {
        // Add a global fetch error handler
        const originalFetch = window.fetch;
        window.fetch = function() {
            return originalFetch.apply(this, arguments)
                .catch(error => {
                    console.error('Network error:', error);
                    showNotification('Network error. Please check your connection.', 'error');
                    hideLoading();
                    throw error;
                });
        };
    }

    // Call this function when the DOM is loaded
    handleApiErrors();

    // Update UI with shop information
    function updateShopInfo(shop) {
        if (!shop) return;
        
        // Update billing header if needed
        const billingHeader = document.querySelector('.billing-header .title');
        if (billingHeader) {
            billingHeader.textContent = `${shop.name} - Billing Management`;
        }
        
        // Add shop information to the view modal
        const companyInfo = document.querySelector('.company-info h2');
        if (companyInfo) {
            companyInfo.innerHTML = `<i class="fas fa-store"></i> ${shop.name}`;
        }
        
        const companyAddress = document.querySelector('.company-info p:first-of-type');
        if (companyAddress) {
            companyAddress.textContent = shop.address;
        }
        
        const companyPhone = document.querySelector('.company-info p:last-of-type');
        if (companyPhone) {
            companyPhone.textContent = `Phone: ${shop.phone}`;
        }
    }

    // Expose the saveBill function globally for the modal_fix.js script
    window.saveBill = saveBill;
    window.closeModal = closeModal;

    // Function to update bill statistics
    function updateBillStatistics() {
        // Add cache busting parameter
        const timestamp = new Date().getTime();
        // Use shopId from URL or currentShopId if available
        const shopIdToUse = shopId || currentShopId || 1;
        const url = `api/bill_statistics.php?shopId=${shopIdToUse}&_cache=${timestamp}`;
        
        console.log('Updating bill statistics, making request to:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Bill statistics received:', data);
            
            if (data.success) {
                // Target the specific stat-card divs using their child values
                // Find all stat-card elements and update based on their labels
                const statCards = document.querySelectorAll('.stat-card');
                
                console.log('Found', statCards.length, 'stat cards');
                
                statCards.forEach(card => {
                    const label = card.querySelector('.stat-label');
                    const valueElement = card.querySelector('.stat-value');
                    
                    if (!label || !valueElement) return;
                    
                    const labelText = label.textContent.trim().toLowerCase();
                    console.log('Processing stat card with label:', labelText);
                    
                    if (labelText.includes('total bills')) {
                        valueElement.textContent = data.total || 0;
                        console.log('Updated total bills to:', data.total);
                    } 
                    else if (labelText.includes('total amount')) {
                        valueElement.textContent = '₹' + parseFloat(data.total_amount || 0).toFixed(2);
                        console.log('Updated total amount to:', data.total_amount);
                    }
                    else if (labelText.includes('pending')) {
                        valueElement.textContent = data.pending || 0;
                        console.log('Updated pending bills to:', data.pending);
                    }
                    else if (labelText.includes('processing')) {
                        valueElement.textContent = data.processing || 0;
                        console.log('Updated processing bills to:', data.processing);
                    }
                    else if (labelText.includes('completed')) {
                        valueElement.textContent = data.completed || 0;
                        console.log('Updated completed bills to:', data.completed);
                    }
                });
                
                console.log('Statistics updated successfully');
            } else {
                console.error('Failed to update bill statistics:', data.message);
            }
        })
        .catch(error => {
            console.error('Error updating bill statistics:', error);
        });
    }

    // Force refresh function that can be called from the UI or from code
    function forceRefresh() {
        console.log('Forcing a full page refresh');
        // Add a notification
        showNotification('Refreshing page...', 'info');
        
        // Use a cache-busting parameter
        const cacheBuster = new Date().getTime();
        window.location.href = window.location.pathname + '?refresh=' + cacheBuster;
    }

    // Add this to the DOMContentLoaded handler at the bottom of the script
    // Create a refresh button if it doesn't exist
    function addRefreshButton() {
        // Don't add it if it already exists
        if (document.getElementById('force-refresh-btn')) return;
        
        const actionBar = document.querySelector('.action-bar') || 
                         document.querySelector('.bills-container') ||
                         document.querySelector('.pagination');
        
        if (actionBar) {
            const refreshBtn = document.createElement('button');
            refreshBtn.id = 'force-refresh-btn';
            refreshBtn.className = 'btn btn-secondary';
            refreshBtn.innerHTML = '<i class="fas fa-sync"></i> Refresh';
            refreshBtn.style.marginLeft = '10px';
            refreshBtn.onclick = forceRefresh;
            
            actionBar.appendChild(refreshBtn);
            console.log('Added refresh button to page');
        }
    }

    // Call this function when the DOM is loaded
    addRefreshButton();

    // Add a more reliable tab switching function
    function switchToTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.getAttribute('data-tab') === tabName) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Set current tab and reload bills
        currentTab = tabName;
        currentPage = 1; // Reset to first page when switching tabs
        loadBills(true); // Force reload
        
        // Update URL parameter
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
        
        console.log(`Switched to tab: ${tabName}`);
    }

    // Add updateBillStatus function after the switchToTab function
    function updateBillStatus(billId, currentStatus) {
        // Determine next status in the cycle: pending -> processing -> completed -> pending
        let nextStatus;
        switch(currentStatus) {
            case 'pending':
                nextStatus = 'processing';
                break;
            case 'processing':
                nextStatus = 'completed';
                break;
            case 'completed':
                nextStatus = 'pending';
                break;
            default:
                nextStatus = 'pending';
        }
        
        // Show a notification about the status change
        showNotification(`Changing status to ${nextStatus}...`, 'info');
        
        // Prepare data for the API
        const statusData = {
            id: billId,
            status: nextStatus,
            status_update: true
        };
        
        // Call the API to update the status
        fetch('api/save_bill.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            body: JSON.stringify(statusData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`Status updated to ${nextStatus}`, 'success');
                
                // Immediately update the UI before reloading
                const statusBadge = document.querySelector(`.status-badge[data-id="${billId}"]`);
                if (statusBadge) {
                    statusBadge.textContent = capitalizeFirst(nextStatus);
                    statusBadge.className = `status-badge status-${nextStatus}`;
                    statusBadge.setAttribute('data-current', nextStatus);
                }
                
                // Force refresh the data and update statistics
                updateBillStatistics();
                
                // If the status is now "completed" and we're not on the completed tab, 
                // the bill will disappear from the current view. Switch to the appropriate tab.
                if (nextStatus === 'completed' && currentTab !== 'completed') {
                    setTimeout(() => {
                        switchToTab('completed');
                    }, 1000);
                } else if (nextStatus === 'pending' && currentTab !== 'pending') {
                    setTimeout(() => {
                        switchToTab('pending');
                    }, 1000);
                } else if (nextStatus === 'processing' && currentTab !== 'recent') {
                    setTimeout(() => {
                        switchToTab('recent');
                    }, 1000);
                } else {
                    // If we're already on the correct tab, just reload the bills
                    setTimeout(() => {
                        loadBills(true);
                    }, 500);
                }
            } else {
                showNotification(data.message || 'Failed to update status', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            showNotification('Error connecting to server', 'error');
        });
    }

    // Initialize with the default tab or tab from URL
    const tabParam = urlParams.get('tab');
    if (tabParam && ['recent', 'pending', 'completed'].includes(tabParam)) {
        switchToTab(tabParam);
    } else {
        switchToTab('recent'); // Default tab
    }
}); 