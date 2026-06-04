<style>
    .main-header .logo {
        transition: width .3s ease-in-out;
        display: block;
        float: left;
        height: 52px;          /* container height */
        width: 230px;          /* container width */
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-weight: 300;
        overflow: hidden;
        padding: 0;
        line-height: 50px;
    }

    .main-header .logo img {
        height: 100%;          /* fill container height */
        width: 100%;           /* fill container width */
        object-fit: contain;   /* scale image preserving aspect ratio */
        display: block;
    }

    @media (max-width: 767px) {
        .main-header .logo {
            width: 120px;
            height: 40px;
        }
        .main-header .logo img {
            height: 40px;
            width: 100%;
        }
        .navbar.navbar-fixed-top {
            height: 3.5rem !important;
        }
    }
</style>

<header class="main-header navbar-fixed-top">
    <a href="dashboard.php" class="logo" style="position: fixed;">
         <img src="../assets/img/logo1.png" alt="Logo">
    </a>
    <nav class="navbar navbar-fixed-top" style="height: 5.2rem;">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" aria-label="Toggle navigation">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
    </nav>
</header>

<script>
// Sidebar toggle for mobile
if (window.innerWidth <= 767) {
    document.querySelector('.sidebar-toggle').addEventListener('click', function(e) {
        e.preventDefault();
        document.body.classList.toggle('sidebar-open');
    });
}
</script>
