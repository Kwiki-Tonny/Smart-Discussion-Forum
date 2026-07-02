// resources/js/app.js

// 1. Laravel's default setup (DO NOT DELETE)
import './bootstrap';

// 2. Our Smart Forum Navigation Logic
document.addEventListener('DOMContentLoaded', () => {
    const groupsView = document.getElementById('groups-view');
    const topicsView = document.getElementById('topics-view');
    const groupTitle = document.getElementById('current-group-title');
    const dynamicAssignment = document.getElementById('dynamic-assignment');

    // We attach these to the 'window' object so our HTML onclick attributes can find them
    window.openGroup = function(groupName) {
        // Update the text in the header
        groupTitle.innerText = groupName;
        dynamicAssignment.innerText = groupName + " Assignments";
        
        // "Flip the card" by animating CSS classes
        groupsView.classList.add('card-hidden');
        groupsView.style.transform = 'translateX(-100%)'; // Slide out left
        
        topicsView.classList.remove('card-hidden');
        topicsView.style.transform = 'translateX(0)';     // Slide in center
    };

    window.goBack = function() {
        // "Flip the card" back
        topicsView.classList.add('card-hidden');
        topicsView.style.transform = 'translateX(100%)';  // Slide out right
        
        groupsView.classList.remove('card-hidden');
        groupsView.style.transform = 'translateX(0)';     // Slide in center
    };
});