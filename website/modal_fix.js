/**
 * Enhanced Modal Fix - Fixed approach for modal functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced modal fix script loaded');
    
    // Get modal elements
    const billModal = document.getElementById('billModal');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const closeBtn = document.querySelector('.close-btn');
    const billForm = document.getElementById('billForm');
    const newBillBtn = document.getElementById('newBillBtn');
    
    if (!billModal || !saveBtn || !cancelBtn || !closeBtn || !billForm || !newBillBtn) {
        console.error('One or more modal elements not found');
        return;
    }
    
    // Clear any existing listeners by cloning and replacing the elements
    function replaceWithClone(element) {
        const clone = element.cloneNode(true);
        element.parentNode.replaceChild(clone, element);
        return clone;
    }
    
    // Replace elements with clones to clear existing event listeners
    const newSaveBtn = replaceWithClone(saveBtn);
    const newCancelBtn = replaceWithClone(cancelBtn);
    const newCloseBtn = replaceWithClone(closeBtn);
    const newBillForm = replaceWithClone(billForm);
    const newNewBillBtn = replaceWithClone(newBillBtn);
    
    // Add direct onclick handlers
    newSaveBtn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Save button clicked (direct)');
        
        // Get the saveBill function from the original context
        if (typeof window.saveBill === 'function') {
            window.saveBill();
        } else {
            console.error('saveBill function not found');
            alert('Unable to save bill. Please try the alternate form.');
        }
        return false;
    };
    
    newCancelBtn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Cancel button clicked (direct)');
        
        // Close the modal manually
        billModal.classList.remove('active');
        setTimeout(() => {
            billModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
        return false;
    };
    
    newCloseBtn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Close button clicked (direct)');
        
        // Close the modal manually
        billModal.classList.remove('active');
        setTimeout(() => {
            billModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
        return false;
    };
    
    newBillForm.onsubmit = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Form submitted (direct)');
        
        // Get the saveBill function from the original context
        if (typeof window.saveBill === 'function') {
            window.saveBill();
        } else {
            console.error('saveBill function not found');
            alert('Unable to save bill. Please try the alternate form.');
        }
        return false;
    };
    
    // Handle opening the modal
    newNewBillBtn.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('New bill button clicked (direct)');
        
        // Show the modal manually
        billModal.style.display = 'block';
        
        // Force a reflow before adding the active class
        void billModal.offsetWidth;
        
        // Add active class for animation
        billModal.classList.add('active');
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
        
        return false;
    };
    
    // Stop propagation on the modal content
    const modalContent = billModal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.onclick = function(e) {
            e.stopPropagation();
            return true; // Allow the event to continue within the content
        };
    }
    
    // Close the modal when clicking outside
    billModal.onclick = function(e) {
        if (e.target === billModal) {
            // Only close if the click is directly on the modal backdrop
            billModal.classList.remove('active');
            setTimeout(() => {
                billModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
    };
    
    console.log('Enhanced modal fix applied');
}); 