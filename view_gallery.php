<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM image ORDER BY created_date DESC";

// $query = "select * from team ORDER BY RAND()";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');
?>
<style>
.section-title span{
    font-size:20px;
    font-weight:500;
    margin-bottom:1rem;
    margin-top:1rem;
}
    .section-title h2 {
        background: -moz-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: -webkit-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: linear-gradient(to bottom, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        border-radius: 7px;
        padding: 10px;
        font-size:1.4rem;
        width:50%;
        color:#fff;
        margin:auto;
         box-shadow: -6px -6px 8px rgb(250 250 250), -2px -1px 8px #FFFFFF, 2px 2px 10px rgb(22 22 20 / 25%), -4px -4px 20px rgb(245 192 93 / 66%), 1px 1px 5px rgb(177 170 152 / 60%), 4px 4px 15px rgb(0 0 0 / 14%);
    }

   

    body {
        background-color: #F5F5F5;
    }

    .single-news {
        width: 100%;
        overflow: hidden;
        padding: 10px;
        border-radius: 10px;
        box-sizing: border-box;
        background-color: #fff;
        background: linear-gradient(133.21deg, #F7F7F7 -2.44%, #F9F9F9 135.62%);
        box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #FFFFFF,
            2px 2px 10px rgba(255, 255, 255, 0.25),
            -4px -4px 20px rgba(255, 255, 255, 0.8),
            1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
    }

    .single-news img {
        width: 100%;
        height: 271px;
        object-fit: cover;
        cursor: pointer;
    }

    /* Modal styles */
    #myModal {
        display: none;
        position: fixed;
        z-index: 100000;
        left: 0;
       top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 800px;
        max-height:700px;
        object-fit:cover;
    }

    .close {
        position: absolute;
        top: 0px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
    
     @media only screen and (max-width:767px) {
        .section-title h2 {
            background-color: var(--main-color);
            border-radius: 7px;
            font-size: 18px;
            padding: 8px 0;
            width:80%;
        }
        #myModel {
            top: 0px;
        }
        .close {
            top: 10px;
            right: 18px;
        }
    }
    /* Styles for the arrow buttons */
.prev, .next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 26px;
    font-weight: bold;
    cursor: pointer;
    user-select: none;
    transition: 0.3s;
    padding: 10px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.5);
}


.prev:hover, .next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Positioning the arrows */
.prev {
    left: 20px;
}

.next {
    right: 20px;
}

/* Mobile responsiveness */
@media only screen and (max-width: 767px) {
    .close {
        top: 10px;
        right: 18px;
    }

    .prev, .next {
        font-size: 30px;
        padding: 8px;
    }
}
</style>

<body>
    <?php
    include('include/header1.php');
    ?>

    <section class="news-area-two pt-75 pb-70">
        <div class="container">
            <div class="section-title">
                <span>Our Beautiful Moments</span>
                <h2>GALLERY</h2>
            </div>
            <div class="row">
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="single-news">
                            <img src="assets/img/gallery/<?= $row['small_image'] ?>" alt="Image" id="<?php $row['id'] ?>"
                                class="modal-content" onclick="openModal(this.src)" style="width:-webkit-fill-available;">
                        </div>
                    </div>

                    <?php
                }
                ?>


            </div>
        </div>
    </section>
    <!-- Modal -->
    <div id="myModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="img011" src="">
        <a class="prev" onclick="changeImage(-1)">❮</a>
        <a class="next" onclick="changeImage(1)">❯</a>
    </div>
    <script>
        // Array to hold the image sources
let images = [];
let currentIndex = 0;

// Populate the images array with the sources of all images in the gallery
document.querySelectorAll('.single-news img').forEach((img, index) => {
    images.push(img.src);
    img.setAttribute('data-index', index);
});

function openModal(src) {
    // Find the current index of the clicked image
    currentIndex = images.indexOf(src);
    document.getElementById("myModal").style.display = "flex";
    document.getElementById("img011").src = src;
}

function closeModal() {
    document.getElementById("myModal").style.display = "none";
}

function changeImage(direction) {
    // Change the current index based on the direction
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = images.length - 1; // Loop to the last image
    if (currentIndex >= images.length) currentIndex = 0; // Loop to the first image
    document.getElementById("img011").src = images[currentIndex];
}

    </script>
    <?php
    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>

</html>