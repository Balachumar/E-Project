let currentUser = null;
let currentPage = 'home';

function loadComponent(componentId, filePath) {
    fetch(filePath)
        .then(response => response.text())
        .then(data => {
            document.getElementById(componentId).innerHTML = data;
            attachEventListeners();
        })
        .catch(error => console.error('Error loading component:', error));
}

function loadPage(pageName) {
    currentPage = pageName;
    const pagePath = `pages/${pageName}.html`;
    
    fetch(pagePath)
        .then(response => response.text())
        .then(data => {
            document.getElementById('page-content').innerHTML = data;
            attachEventListeners();
            updateActiveNav(pageName);
        })
        .catch(error => console.error('Error loading page:', error));
}

function updateActiveNav(pageName) {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-page') === pageName) {
            link.classList.add('active');
        }
    });
}

function attachEventListeners() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageId = this.getAttribute('data-page');
            loadPage(pageId);
        });
    });
    
    document.querySelectorAll('.dashboard-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const dashboardId = this.getAttribute('data-dashboard');
            document.querySelectorAll('.dashboard-link').forEach(item => {
                item.classList.remove('active');
            });
            
            this.classList.add('active');
            const parentDashboard = this.closest('.dashboard-content');
            if (parentDashboard) {
                parentDashboard.querySelectorAll('.dashboard-page').forEach(page => {
                    page.classList.remove('active');
                });
                const targetPage = parentDashboard.querySelector(`#${dashboardId}`);
                if (targetPage) {
                    targetPage.classList.add('active');
                }
            }
        });
    });
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            currentUser = { role: 'user', name: 'Jessica Smith' };
            loadPage('user-dashboard');
        });
    }
    
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            currentUser = { role: 'admin', name: 'Admin' };
            loadPage('admin-dashboard');
        });
    }
    
    const logoutLinks = document.querySelectorAll('.logout-link');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            currentUser = null;
            loadPage('home');
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadComponent('header', 'components/header.html');
    loadComponent('footer', 'components/footer.html');
    loadPage('home');
});