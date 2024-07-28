<?php 
require_once __DIR__.'./header.php';
require_once 'db_connect.php';

// Fetching data from the database
// Fetch total clients
$totalClientsQuery = $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE rôle = 'client'");
$totalClients = $totalClientsQuery->fetchColumn();

// Fetch total services
$totalServicesQuery = $conn->query("SELECT COUNT(*) FROM services");
$totalServices = $totalServicesQuery->fetchColumn();

// Fetch total employees
$totalEmployeesQuery = $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE rôle = 'employe'");
$totalEmployees = round($totalEmployeesQuery->fetchColumn(), 1);

// Fetch total demandes
$totalDemandesQuery = $conn->query("SELECT COUNT(*) FROM demandes");
$totalDemandes = $totalDemandesQuery->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SahlaMahla</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<!-- Carousel Start -->
<div class="container-fluid px-0">
    <div id="carouselId" class="carousel slide" data-bs-ride="carousel">
        <ol class="carousel-indicators">
            <li data-bs-target="#carouselId" data-bs-slide-to="0" class="active" aria-current="true" aria-label="First slide"></li>
            <li data-bs-target="#carouselId" data-bs-slide-to="1" aria-label="Second slide"></li>
        </ol>
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item active" style="height: 550px;">
                <img src="img/carousel-1.jpg" class="img-fluid" alt="First slide">
                <div class="carousel-caption">
                    <div class="container carousel-content">
                        <h6 class="text-secondary h4 animated fadeInUp">Nous facilitons la gestion de votre maison</h6>
                        <h1 class="text-white display-1 mb-4 animated fadeInRight">Des Solutions Pionnières pour Vos Besoins à Domicile</h1>
                        <p class="mt-4">Sahla Mahla est une agence qui vous permet de trouver facilement des services à domicile tels que le travail de bricoleur, la garde d’enfants, l’entretien ménager et bien plus encore. Nous nous engageons à vous fournir des services de qualité pour vous faciliter la vie.</p>
                        <a href="" class="me-2"><button type="button" class="px-4 py-sm-3 px-sm-5 btn btn-primary rounded-pill carousel-content-btn1 animated fadeInLeft">Plus</button></a>
                        <a href="" class="ms-2"><button type="button" class="px-4 py-sm-3 px-sm-5 btn btn-primary rounded-pill carousel-content-btn2 animated fadeInRight">Contactez-nous</button></a>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="height: 550px;">
                <img src="img/carousel-2.jpg" class="img-fluid" alt="Second slide">
                <div class="carousel-caption">
                    <div class="container carousel-content">
                        <h6 class="text-secondary h4 animated fadeInUp">La Sérénité Chez Vous</h6>
                        <h1 class="text-white display-1 mb-4 animated fadeInRight">Des Prestations de Qualité pour un Confort Quotidien</h1>
                        <p class="mt-4">Sahla Mahla facilite votre recherche de services à domicile en vous connectant avec des experts en bricolage, garde d’enfants, et entretien ménager. Nous nous efforçons de vous offrir des solutions pratiques et fiables pour un quotidien sans souci.</p>
                        <a href="" class="me-2"><button type="button" class="px-4 py-sm-3 px-sm-5 btn btn-primary rounded-pill carousel-content-btn1 animated fadeInLeft">Plus</button></a>
                        <a href="" class="ms-2"><button type="button" class="px-4 py-sm-3 px-sm-5 btn btn-primary rounded-pill carousel-content-btn2 animated fadeInRight">Contactez-nous</button></a>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselId" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselId" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<!-- Carousel End -->

<!-- Fact Start -->
<div class="container-fluid bg-secondary py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 wow fadeIn" data-wow-delay=".1s">
                <div class="d-flex counter">
                    <h1 class="me-3 text-primary counter-value"><?= $totalClients ?></h1>
                    <h5 class="text-white mt-1">Clients satisfaits</h5>
                </div>
            </div>
            <div class="col-lg-3 wow fadeIn" data-wow-delay=".3s">
                <div class="d-flex counter">
                    <h1 class="me-3 text-primary counter-value"><?= $totalServices ?></h1>
                    <h5 class="text-white mt-1">Services offerts</h5>
                </div>
            </div>
            <div class="col-lg-3 wow fadeIn" data-wow-delay=".5s">
                <div class="d-flex counter">
                    <h1 class="me-3 text-primary counter-value"><?= $totalEmployees ?></h1>
                    <h5 class="text-white mt-1">Employés</h5>
                </div>
            </div>
            <div class="col-lg-3 wow fadeIn" data-wow-delay=".7s">
                <div class="d-flex counter">
                    <h1 class="me-3 text-primary counter-value"><?= $totalDemandes ?></h1>
                    <h5 class="text-white mt-1">Demandes reçues</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fact End -->

<!-- About Start -->
<div class="container-fluid py-5 my-5">
    <div class="container pt-5">
        <div class="row g-5">
            <div class="col-lg-5 col-md-6 col-sm-12 wow fadeIn" data-wow-delay=".3s">
                <div class="h-100 position-relative">
                    <img src="img/about-1.jpg" class="img-fluid w-75 rounded" alt="" style="margin-bottom: 25%;">
                    <div class="position-absolute w-75" style="top: 25%; left: 25%;">
                        <img src="img/about-2.jpg" class="img-fluid w-100 rounded" alt="">
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-6 col-sm-12 wow fadeIn" data-wow-delay=".5s">
                <h5 class="text-primary">À propos</h5>
                <h1 class="mb-4">À propos de SahlaMahla</h1>
                <p class="mt-4">Sahla Mahla est une agence spécialisée qui simplifie la recherche et l'accès à une gamme variée de services à domicile essentiels. Que ce soit pour des besoins de bricolage, de garde d’enfants, d’entretien ménager ou d'autres services pratiques, nous nous engageons à vous offrir des solutions de haute qualité qui améliorent votre quotidien. Notre mission est de rendre votre vie plus facile en vous connectant avec des professionnels qualifiés et fiables, prêts à répondre à vos besoins avec efficacité et professionnalisme. Chez Sahla Mahla, nous croyons fermement à la satisfaction du client et à l'excellence du service, garantissant ainsi une expérience sans tracas à chaque étape de votre demande de service.</p>
                <a href="contact.php" class="btn btn-secondary rounded-pill px-5 py-3 text-white">Plus de détails</a>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<?php require_once __DIR__.'./footer.php';?>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.1/js/bootstrap.min.js"></script>
</body>
</html>
