<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];

// $query = "SELECT * FROM team ORDER BY created_date DESC";
$query = "SELECT * FROM team WHERE status = 1 ORDER BY created_date DESC";


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
     .profile-btn{
            margin-top: 12px;
         padding: 7px 16px;
        }
    @media only screen and (max-width:767px) {
        .section-title h2 {
            background-color: var(--main-color);
            border-radius: 7px;
            font-size: 20px;
            padding: 8px 0;
        }
         .profile-btn{
             margin-top: 30px;
         padding: 10px 16px;
        }
    }
   
</style>

<body>
    <?php
    include('include/header1.php');
    ?>

    <section class="teachers-area-three pt-20">
        <div class="container">
            <div class="section-title">
                <span>OUR TEAM</span>
                <h2>Meet Our Team Members</h2>
            </div>
            <div class="row">
                 <?php while ($row = mysqli_fetch_assoc($result)) {
                    // $bannerImagePath = $base_url . "/assets/img/team/{$row['image']}";
                    $bannerImagePath = "assets/img/team/" . rawurlencode($row['image']);
                    ?>
                
                <div class="col-lg-3 col-sm-6">
                    <div class="single-teachers shadow">
                        <a href="team/<?= $row['url'] ?>" >
                        <img src="<?= $bannerImagePath ?>" alt="Image" loading="lazy">
                        </a>
                        <div class="teachers-content" >
                       
                            <h5 style="font-size: 17px;"><a href="team/<?= $row['url'] ?>" ><?= $row['name']; ?></a></h5>
                            <span><?= $row['specialisation']; ?></span>
                            <a href="team/<?= $row['url'] ?>" class="default-btn profile-btn">
                                View Profile
                            </a>
                          
                        </div>
                    </div>
                </div>
                <?php
                 }
                 ?>
                
              
                
            </div>
        </div>
    </section>

    <?php
    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>

</html>