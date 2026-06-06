<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="zxx">
<?php
include('include/head.php');
?>
<style>
    .single-teachers {
        background-color: #fff;
        background: linear-gradient(133.21deg, #F7F7F7 -2.44%, #F9F9F9 135.62%);
        box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #FFFFFF,
            2px 2px 10px rgba(255, 255, 255, 0.25),
            -4px -4px 20px rgba(255, 255, 255, 0.8),
            1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        box-sizing: border-box;
    } 
    .single-affordable:hover {
        transform: none;
        background-color: transparent;
        box-shadow: none;
    }
    .p-about {
        font-size: 16px;
        text-align: justify;
        line-height: 1.4;
    }
    @media (max-width: 1024px) {
        .page-title-area.bg-1 {
            padding: 80px;
            margin-top: 0px;
        }
    }
    @media (max-width: 760px) {
        .font-14-mobile {
            font-size: 14px;
        }
        .page-title-content h2 {
            font-size: 1.4rem;
        }
        .hm-vc-btn {
            margin-left: 0;
        }
        .p-about {
            font-size: 14px;
        }
    }
    @media only screen and (max-width: 767px) {
        .single-teachers, .single-affordable {
            box-shadow: none;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .single-teachers img, .education-img img, .education-img-2 img {
            width: 100% !important;
            height: auto !important;
            object-fit: cover;
        }
        .education-content, .course-content {
            height: auto !important;
            padding: 10px 5px !important;
        }
        .btn, .default-btn, button {
            width: 100% !important;
            font-size: 15px !important;
            margin-bottom: 10px;
        }
        .form-control {
            width: 100% !important;
            font-size: 15px !important;
        }
        .section-title h2, .section-title span {
            font-size: 18px !important;
        }
        .page-title-area, .affordable-area, .education-area-two {
            padding: 15px 0 !important;
        }
        .row, .container, .container-fluid {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .col-lg-6, .col-lg-4, .col-md-6, .col-sm-6, .col-lg-3 {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            margin-bottom: 15px;
        }
        .p-about {
            font-size: 13px !important;
        }
    }
</style>
<body>
<?php include('include/header1.php'); ?>
<div class="page-title-area bg-1">
    <div class="container">
        <div class="page-title-content">
            <h2 style="color:white;font-size:3rem">Know About Us</h2>
        </div>
    </div>
</div>
<section class="education-area-two pt-100 pb-50" style="padding-top:20px;">
    <div class="container">
    <div class="section-title">
            <span>About Us</span>
            <h2 class="pad-0" style="padding:0;">Who We Are</h2>
        </div>
        <div class="row row-reverse-custom">
            <div class="col-lg-6">
                <div class="education-img-wrap" style="position: relative;">
                                         <div class="education-img-2" style="position: relative; overflow: hidden; border-radius: 15px; ">
                         <img src="/assets/img/imag/about2.webp" alt="Image" loading="lazy" style="border-radius: 15px; transition: transform 0.3s ease; width: 100%; height: 100%; object-fit: cover;">
                     </div>
                    <div style="position: absolute; top: -10px; right: -10px; width: 50px; height: 50px; background: #fdc134; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 15px rgba(253, 193, 52, 0.3);">
                        <i class="bx bx-star" style="color: white; font-size: 20px;"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="education-content" style="padding: 30px; background: #ffffff; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-left: 4px solid #fdc134;">
                    <span style="color: #fdc134; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 15px;">Our Visionary Founder</span>
                    <h3 style="font-size: 1.8rem; line-height: 1.3; margin-bottom: 20px; color: #2c3e50; font-weight: 700;">MANISH SHARMA GURU JI</h3>
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <div style="width: 30px; height: 2px; background: #fdc134; margin-right: 15px;"></div>
                        <span style="color: #6c757d; font-style: italic; font-size: 14px;">Spiritual Master & Healer</span>
                    </div>
                    <p class="font-14-mobile p-about" style="line-height: 1.7; color: #495057; font-size: 15px; text-align: justify;">Founder of Second sight Foundation and <b style="color: #fdc134;">DURGA REIKI</b>. A <b style="color: #fdc134;">Homoeopathic Physician</b>, <b style="color: #fdc134;">Reiki Grandmaster</b> and multimodality healer with expertise in <b style="color: #fdc134;">Sanjeevani</b>, <b style="color: #fdc134;">Kundalini</b>, <b style="color: #fdc134;">Hypnosis</b>, past life healing, <b style="color: #fdc134;">Mid brain activation</b>, Yoga, Naturopathy, and <b style="color: #fdc134;">MD in Alternative medicine</b>. A spiritual scientist with mastery in 56+ modalities, continuously researching energies and helping people progress spiritually while maintaining worldly balance.</p>
                    <div style="margin-top: 25px; padding: 15px; background: rgba(253, 193, 52, 0.1); border-radius: 10px; border-left: 3px solid #fdc134;">
                        <p style="margin: 0; font-style: italic; color: #495057; font-size: 14px;">"Transforming lives through spiritual awakening and holistic healing"</p>
                    </div>
                </div>
            </div>
        </div>
        <div class=" row mt-5 pt-60 " style='padding-top:100px'>
            <div class="col-lg-6">
                <div class="education-content" style="padding: 30px; background: #ffffff; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-left: 4px solid #fdc134;">
                    <span style="color: #fdc134; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 15px;">About Our Foundation</span>
                    <h3 style="font-size: 1.8rem; line-height: 1.3; margin-bottom: 20px; color: #2c3e50; font-weight: 700;">SECOND SIGHT FOUNDATION</h3>
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <div style="width: 30px; height: 2px; background: #fdc134; margin-right: 15px;"></div>
                        <span style="color: #6c757d; font-style: italic; font-size: 14px;">Spiritual Learning Center</span>
                    </div>
                    <p class="p-about font-14-mobile" style="line-height: 1.7; color: #495057; font-size: 15px; text-align: justify;">Founded by <b style="color: #fdc134;">GURUJI MANISH SHARMA</b>, SSF offers comprehensive spiritual courses including <b style="color: #fdc134;">Third eye awakening</b>, <b style="color: #fdc134;">Usui & Durga Reiki</b>, <b style="color: #fdc134;">Child Development</b>, <b style="color: #fdc134;">Past Life Regression</b>, NLP, and Tarot reading. We also provide healing programs for <b style="color: #fdc134;">Anxiety Depression</b> and <b style="color: #fdc134;">Diabetes Reversal</b> using alternative medicines, Homoeopathy, Diet, and Yoga. Our free meditations and healing sessions have transformed thousands of lives globally.</p>
                    <div style="margin-top: 25px; padding: 15px; background: rgba(253, 193, 52, 0.1); border-radius: 10px; border-left: 3px solid #fdc134;">
                        <p style="margin: 0; font-style: italic; color: #495057; font-size: 14px;">"Empowering individuals worldwide through spiritual wisdom and transformative education"</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="education-img-wrap" style="position: relative;">
                                         <div class="education-img-2" style="position: relative; overflow: hidden; border-radius: 15px;
                                         ">
                         <img src="/assets/img/imag/banner-about-md.webp" alt="Image" loading="lazy" style="border-radius: 15px; transition: transform 0.3s ease; width: 100%; height: 100%; object-fit: cover;">
                     </div>
                    <div style="position: absolute; top: -10px; left: -10px; width: 50px; height: 50px; background: #fdc134; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 15px rgba(253, 193, 52, 0.3);">
                        <i class="bx bx-award" style="color: white; font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="affordable-area  pt-100 pb-70">
     <div class="section-title">
            <span>Why Choose Us</span>
            <h2 class="pad-0" style="padding:0;">Your Benefits with Third Eye Activation Course</h2>
        </div>
<div class="container ">
       
        <div class="row">
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable one" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-investment" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Save Time and Money also</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">Our unique course delivers lifetime access with a one-time fee. Learn at your own pace, without recurring costs, making it a wise investment in your personal growth.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable two" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-balance" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Balance Spiritual Growth with Life</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">Our flexible course schedule helps you integrate powerful spiritual practices without disrupting your daily life, giving you the balance between worldly responsibilities and self-discovery.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable three" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-online-education" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Gain Unique Spiritual Knowledge</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">Unlock the mysteries of the universe, explore advanced spiritual techniques like Shambhavi healing, aura reading, and third-eye activation, giving you powerful tools for self-realization.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable four" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-route" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Lifetime Learning & Progress</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">With lifetime access to our course, attend online or offline workshops at your convenience. Keep growing spiritually while continuously enhancing your intuitive abilities.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable five" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-online-education" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Expert Guidance & Support</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">Learn directly from experienced spiritual masters and healers who have dedicated their lives to helping others achieve spiritual awakening and personal transformation.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="single-affordable six" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 3px solid #ffd7005c; border-radius: 20px; padding: 35px 25px; text-align: center; box-shadow: 0 8px 25px rgba(255, 215, 0, 0.15); transition: all 0.4s ease; height: 300px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background: linear-gradient(45deg, #FFD700, #FFA500); border-radius: 50%; opacity: 0.1;"></div>
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <i class="flaticon-investment" style="color: #FFD700; font-size: 60px; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);"></i>
                        <h3 style="color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 18px;">Transform Your Life</h3>
                        <p class="font-14-mobile" style="color: #6c757d; line-height: 1.6; margin: 0;">Experience profound personal transformation through our comprehensive spiritual programs designed to awaken your inner potential and create lasting positive change.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="teachers-area-three ">
    <div class="container">
        <div class="section-title">
            <span>OUR TEAM</span>
            <h2>Meet Our Team Members</h2>
        </div>
        <div class="row">
            <?php
            $query_team = "SELECT * FROM team ORDER BY created_date DESC limit 4";
            $result_team = mysqli_query($conn, $query_team);
            while ($row_team = mysqli_fetch_assoc($result_team)) {
                // $bannerImagePath = $base_url . "/assets/img/team/{$row_team['image']}";
                      $bannerImagePath = "/assets/img/team/" . rawurlencode($row_team['image']);

            ?>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-teachers shadow">
                        <a href="team/<?= $row_team['url'] ?>">
                            <img src="<?= $bannerImagePath ?>" alt="Image" loading="lazy">
                        </a>
                        <div class="teachers-content" style="height: 185px;">
                            <h5 style="font-size: 17px;"><a href="team/<?= $row_team['url'] ?>"><?= $row_team['name']; ?></a></h5>
                            <span class="font-14-mobile"><?= $row_team['specialisation']; ?></span>
                            <a href="team/<?= $row_team['url'] ?>" class="default-btn hm-vc-btn" style="margin-top: 12px;padding: 6px 20px;">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
<div style="display: flex; justify-content: center; align-items: baseline; height: 100px;">
    <a href="teamlist.php" class="default-btn hm-vc-btn">View Teams</a>
</div>
<?php
include('include/footer.php');
include('include/footer-script.php');
?>
</body>
</html>