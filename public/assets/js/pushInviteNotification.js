function updateNotificationCount() {
    const countElement = document.querySelector('.notification-count');
    const unreadItems = document.querySelectorAll('#notification-popup li[data-id]:not(.read)'); 
    
    if (unreadItems.length > 0) {
        if (!countElement) {
            const span = document.createElement('span');
            span.className = 'notification-count';
            span.textContent = unreadItems.length;
            document.getElementById('notification-icon').appendChild(span);
        } else {
            countElement.textContent = unreadItems.length;
        }
    } else if (countElement) {
        countElement.remove();
    }
} 

function markNotificationAsRead(notificationId) {
    fetch('/notification/read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'id': notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const item = document.querySelector(`li[data-id="${notificationId}"]`);
            if (item) {
                item.classList.add('read'); // Mark the item as read (you might want to style .read differently)
            }
            updateNotificationCount();
            console.log(data.message);
        } else {
            console.error(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

document.addEventListener('DOMContentLoaded', function() {

    const notificationIcon = document.getElementById('notification-icon');
    const notificationPopup = document.getElementById('notification-popup');

    notificationIcon.addEventListener('click', function() {
        if (notificationPopup.classList.contains('hidden')) {
            notificationPopup.classList.remove('hidden');

            const notificationItems = notificationPopup.querySelectorAll('li[data-id]:not(.read)'); // Fetch only the unread items.
            notificationItems.forEach(item => {
                const notificationId = item.getAttribute('data-id');
                markNotificationAsRead(notificationId);
            });
        } else {
            notificationPopup.classList.add('hidden');
        }
    });

});