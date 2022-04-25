@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

    @include('layouts.parts.front-header')



    <section class="hero-section" id="hero">

        <div class="wave">

            <svg width="100%" height="355px" viewBox="0 0 1920 355" version="1.1" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <g id="Apple-TV" transform="translate(0.000000, -402.000000)" fill="#FFFFFF">
                        <path d="M0,439.134243 C175.04074,464.89273 327.944386,477.771974 458.710937,477.771974 C654.860765,477.771974 870.645295,442.632362 1205.9828,410.192501 C1429.54114,388.565926 1667.54687,411.092417 1920,477.771974 L1920,757 L1017.15166,757 L0,757 L0,439.134243 Z"
                              id="Path"></path>
                    </g>
                </g>
            </svg>

        </div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 hero-text-image">
                    <div class="row">
                        <div class="col-lg-8 text-center text-lg-start">
                            <h1 data-aos="fade-right">Skutecznie przygotuj się do
                                egzaminu z ius vitae</h1>
                            <p class="mb-5" data-aos="fade-right" data-aos-delay="100">Innowacyjna platforma edukacyjna.</p>
                            <p data-aos="fade-right" data-aos-delay="200" data-aos-offset="-500"><a href="#"
                                                                                                    class="btn btn-outline-white">Get
                                    started</a></p>
                        </div>
                        <div class="col-lg-4 iphone-wrap d-none">
                            <img src="/img/phone_1.png" alt="Image" class="phone-1" data-aos="fade-right">
                            <img src="/img/phone_2.png" alt="Image" class="phone-2" data-aos="fade-right"
                                 data-aos-delay="200">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section><!-- End Hero -->

    <main id="main">
        <!-- ======= Portfolio Section ======= -->
        <section id="portfolio" class="portfolio">
            <div class="container" data-aos="fade-up">
                <div class="row justify-content-center text-center mb-5">
                    <div class="col-md-8 aos-init aos-animate" data-aos="fade-up">
                        <h2 class="section-heading">Przeglądaj akty prawne</h2>
                        <p class="mt-4">Baza ponad 100 aktów prawnych codziennie aktualizowanych. Nie wymaga rejestracji ani
                            kupowania pakietu</p>
                    </div>
                </div>

                <div class="row gy-4 portfolio-container" data-aos="fade-up" data-aos-delay="200">

                    <div class="col-lg-4 col-md-6 portfolio-item filter-app">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-1.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>Stale powiększana baza kilkunastu tysięcy pytań testowych</h4>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-1.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="Nauka z dowolnego miejsca"><i
                                            class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 portfolio-item filter-web">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-2.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>Web 3</h4>
                                <p>Web</p>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-2.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="Web 3"><i class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 portfolio-item filter-app">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-3.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>App 2</h4>
                                <p>App</p>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-3.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="App 2"><i class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

            </div>

        </section>
        <!-- End Portfolio Section -->


        <!-- ======= Portfolio Section ======= -->
        <section id="portfolio1" class="portfolio">

            <div class="container" data-aos="fade-up">

                <div class="row justify-content-center text-center mb-5">
                    <div class="col-md-8 aos-init aos-animate" data-aos="fade-up">
                        <h2 class="section-heading">Słuchaj aktów prawnych czytanych przez lektora</h2>
                        <p class="mt-4">Kupując wybrany pakiet uzyskujesz możliwość odsłuchiwania aktów prawnych zawartych w
                            pakiecie</p>
                    </div>
                </div>

                <div class="row gy-4 portfolio-container" data-aos="fade-up" data-aos-delay="200">

                    <div class="col-lg-4 col-md-6 portfolio-item filter-app">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-1.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>Stale powiększana baza kilkunastu...</h4>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-1.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="Stale powiększana baza kilkunastu tysięcy pytań testowych"><i
                                            class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 portfolio-item filter-web">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-2.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>Web 3</h4>
                                <p>Web</p>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-2.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="Web 3"><i class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 portfolio-item filter-app">
                        <div class="portfolio-wrap">
                            <img src="/img/portfolio/portfolio-3.jpg" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>App 2</h4>
                                <p>App</p>
                                <div class="portfolio-links">
                                    <a href="/img/portfolio/portfolio-3.jpg" data-gallery="portfolioGallery"
                                       class="portfokio-lightbox" title="App 2"><i class="bi bi-plus"></i></a>
                                    <a href="portfolio-details.html" title="More Details"><i class="bi bi-link"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

            </div>

        </section>
        <!-- End Portfolio Section -->


        <!-- ======= Counts Section ======= -->
        <section id="counts" class="counts">
            <div class="container" data-aos="fade-up">

                <div class="row gy-4">

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-emoji-smile"></i>
                            <div>
                            <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="1"
                                  class="purecounter"></span>
                                <p>Happy Clients</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-journal-richtext" style="color: #ee6c20;"></i>
                            <div>
                            <span data-purecounter-start="0" data-purecounter-end="123" data-purecounter-duration="1"
                                  class="purecounter"></span>
                                <p>Legal Documents</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-headset" style="color: #15be56;"></i>
                            <div>
                            <span data-purecounter-start="0" data-purecounter-end="10" data-purecounter-duration="1"
                                  class="purecounter"></span>
                                <p>Audio Packages</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="count-box">
                            <i class="bi bi-calendar3" style="color: #bb0852;"></i>
                            <div>
                            <span data-purecounter-start="0" data-purecounter-end="1256" data-purecounter-duration="1"
                                  class="purecounter"></span>
                                <p>Days Of Support</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section><!-- End Counts Section -->


        <!-- ======= Pricing Section ======= -->
        <section id="pricing" class="pricing">

            <div class="container" data-aos="fade-up">

                <div class="row justify-content-center text-center mb-5">
                    <div class="col-md-8 aos-init aos-animate" data-aos="fade-up">
                        <h2 class="section-heading">Nasza oferta</h2>
                    </div>
                </div>

                <div class="row gy-4" data-aos="fade-left">

                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                        <div class="box">
                            <h3 style="color: #07d5c0;">Konstytucja</h3>
                            <div class="price"><sup>$</sup>0<span> / mo</span></div>
                            <img src="/img/pricing-free.png" class="img-fluid" alt="">
                            <p>
                                Darmowy pakiet z pytaniami z Konstytucji
                            </p>
                            <a href="#" class="btn-buy">Buy Now</a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                        <div class="box">
                            <span class="featured">Featured</span>
                            <h3 style="color: #65c600;">Aplikacja sędziowska i prokuratorska</h3>
                            <div class="price"><sup>$</sup>19<span> / mo</span></div>
                            <img src="/img/pricing-starter.png" class="img-fluid" alt="">
                            <p>
                                Pakiet zawiera ponad 9500 pytań przygotowujących do egzaminu na aplikację sędziowską i
                                prokuratorską
                            </p>
                            <a href="#" class="btn-buy">Buy Now</a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                        <div class="box">
                            <h3 style="color: #ff901c;">Aplikacja uzupełniająca sędziowska</h3>
                            <div class="price"><sup>$</sup>29<span> / mo</span></div>
                            <img src="/img/pricing-business.png" class="img-fluid" alt="">
                            <p>
                                Pakiet zawiera ponad 8500 pytań przygotowujących do egzaminu na aplikację uzupełniającą
                                sędziowską. Pytania dotyczą aktów prawnych zawartych w wykazie na egzamin w 2022 r.
                            </p>
                            <a href="#" class="btn-buy">Buy Now</a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                        <div class="box">
                            <h3 style="color: #ff0071;">Aplikacja radcowska i adwokacka</h3>
                            <div class="price"><sup>$</sup>49<span> / mo</span></div>
                            <img src="/img/pricing-ultimate.png" class="img-fluid" alt="">
                            <p>Pakiet zawiera ponad 11 000 pytań przygotowujących do egzaminu na aplikację radcowską i
                                aplikację adwokacką</p>
                            <a href="#" class="btn-buy">Buy Now</a>
                        </div>
                    </div>


                </div>

            </div>

        </section><!-- End Pricing Section -->


        <!-- ======= Testimonials Section ======= -->
        <section id="testimonials" class="testimonials d-none">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h2>Testimonials</h2>
                    <p>What they are saying about us</p>
                </header>

                <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="200">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit
                                    rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam,
                                    risus at semper.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                                    <h3>Saul Goodman</h3>
                                    <h4>Ceo &amp; Founder</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid
                                    cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet
                                    legam anim culpa.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                                    <h3>Sara Wilsson</h3>
                                    <h4>Designer</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam
                                    duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                                    <h3>Jena Karlis</h3>
                                    <h4>Store Owner</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat
                                    minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore
                                    labore illum veniam.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
                                    <h3>Matt Brandon</h3>
                                    <h4>Freelancer</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                        class="bi bi-star-fill"></i>
                                </div>
                                <p>
                                    Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster
                                    veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam
                                    culpa fore nisi cillum quid.
                                </p>
                                <div class="profile mt-auto">
                                    <img src="/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
                                    <h3>John Larson</h3>
                                    <h4>Entrepreneur</h4>
                                </div>
                            </div>
                        </div><!-- End testimonial item -->

                    </div>
                    <div class="swiper-pagination"></div>
                </div>

            </div>

        </section><!-- End Testimonials Section -->

        <!-- ======= Recent Blog Posts Section ======= -->
        <section id="recent-blog-posts" class="recent-blog-posts d-none">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h2>Blog</h2>
                    <p>Recent posts form our Blog</p>
                </header>

                <div class="row">

                    <div class="col-lg-4">
                        <div class="post-box">
                            <div class="post-img"><img src="/img/blog/blog-1.jpg" class="img-fluid" alt=""></div>
                            <span class="post-date">Tue, September 15</span>
                            <h3 class="post-title">Eum ad dolor et. Autem aut fugiat debitis voluptatem consequuntur
                                sit</h3>
                            <a href="blog-single.html" class="readmore stretched-link mt-auto"><span>Read More</span><i
                                    class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="post-box">
                            <div class="post-img"><img src="/img/blog/blog-2.jpg" class="img-fluid" alt=""></div>
                            <span class="post-date">Fri, August 28</span>
                            <h3 class="post-title">Et repellendus molestiae qui est sed omnis voluptates magnam</h3>
                            <a href="blog-single.html" class="readmore stretched-link mt-auto"><span>Read More</span><i
                                    class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="post-box">
                            <div class="post-img"><img src="/img/blog/blog-3.jpg" class="img-fluid" alt=""></div>
                            <span class="post-date">Mon, July 11</span>
                            <h3 class="post-title">Quia assumenda est et veritatis aut quae</h3>
                            <a href="blog-single.html" class="readmore stretched-link mt-auto"><span>Read More</span><i
                                    class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                </div>

            </div>

        </section><!-- End Recent Blog Posts Section -->

        <!-- ======= Contact Section ======= -->
        <section id="contact" class="contact d-none">

            <div class="container" data-aos="fade-up">

                <header class="section-header">
                    <h2>Contact</h2>
                    <p>Contact Us</p>
                </header>

                <div class="row gy-4">

                    <div class="col-lg-6">

                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="bi bi-geo-alt"></i>
                                    <h3>Address</h3>
                                    <p>A108 Adam Street,<br>New York, NY 535022</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="bi bi-telephone"></i>
                                    <h3>Call Us</h3>
                                    <p>+1 5589 55488 55<br>+1 6678 254445 41</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="bi bi-envelope"></i>
                                    <h3>Email Us</h3>
                                    <p>info@example.com<br>contact@example.com</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <i class="bi bi-clock"></i>
                                    <h3>Open Hours</h3>
                                    <p>Monday - Friday<br>9:00AM - 05:00PM</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-6">
                        <form action="forms/contact.php" method="post" class="php-email-form">
                            <div class="row gy-4">

                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>

                                <div class="col-md-6 ">
                                    <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                                </div>

                                <div class="col-md-12">
                                    <input type="text" class="form-control" name="subject" placeholder="Subject" required>
                                </div>

                                <div class="col-md-12">
                                <textarea class="form-control" name="message" rows="6" placeholder="Message"
                                          required></textarea>
                                </div>

                                <div class="col-md-12 text-center">
                                    <div class="loading">Loading</div>
                                    <div class="error-message"></div>
                                    <div class="sent-message">Your message has been sent. Thank you!</div>

                                    <button type="submit">Send Message</button>
                                </div>

                            </div>
                        </form>

                    </div>

                </div>

            </div>

        </section><!-- End Contact Section -->

    </main><!-- End #main -->


    <div class="yh-v-p-r d-none">

        <div class="features">
            <div class="yh-gap-10"></div>
            <div class="text-center">
                <h2>
                <span class="big">
                    Zalety
                </span>
                    <span class="sub">
                    Dlaczego warto nam zaufać
                </span>
                </h2>

            </div>
            <div class="yh-gap-6"></div>

            <div class="container">
                <div class="row">
                    @foreach($promotions as $promotion)
                        <div class="col-md-4 col-sm-6 col-xs-12" id="promotion-{{$promotion->id}}"
                             onclick="open_promotion('{{$promotion->id}}')">
                            <div class="features-box">
                                <img class="thumbnail" src="{{ $promotion->image }}" alt="{{ $promotion->title }}">
                                <h3 class="title">{{ $promotion->title }}</h3>
                                <div class="d-none context">{!! $promotion->full !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="yh-gap-10"></div>
        </div>

        <div class="packages">

            <div class="yh-gap-10"></div>
            <h2 class="text-center">Dostępne pakiety egzaminacyjne</h2>
            <div class="text-center"><em>Pakiet Konstytucja dostępny za darmo bezterminowo</em></div>
            <div class="yh-gap-6"></div>

            <div class="container">
                <a name="pakiety"></a>
                <div class="carousel row">
                    @foreach($packages as $p)
                        @if($p->visible)
                            @if($p->free & $p->name == 'Konstytucja')
                                <div class="carousel-cell col-lg-6 p-3">
                                    <a class="carousel-cell-inner"
                                       onclick="document.location='{{route('app',['any'=>'shop/form/'.$p->id.'/1'])}}'">
                                        <h3>{{$p->name}}</h3>
                                        <p>{{$p->info}}</p>
                                        @if($p->free)
                                            <strong>darmowy</strong>
                                        @else
                                            <strong>od {{$p->price1m}} zł </strong>
                                        @endif
                                    </a>
                                </div>
                            @endif
                        @endif
                    @endforeach
                    @foreach($packages as $p)
                        @if($p->visible)
                            @if(!($p->free & $p->name == 'Konstytucja'))
                                <div class="carousel-cell col-lg-6 p-3">
                                    <a class="carousel-cell-inner"
                                       onclick="document.location='{{route('app',['any'=>'shop/form/'.$p->id.'/1'])}}'">
                                        <h3>{{$p->name}}</h3>
                                        <p>{{$p->info}}</p>
                                        @if($p->free)
                                            <strong>darmowy</strong>
                                        @else
                                            <strong>od {{$p->price1m}} zł </strong>
                                        @endif
                                    </a>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="yh-gap-10"></div>
        </div>


        @include('layouts.parts.front-footer')

    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modal-promotion" data-backdrop="true"
         aria-labelledby="modal-cookies" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 768px;">
            <div class="modal-content bg-dark-dark">
                <div class="modal-body">
                    <img id="modal-promotion-thumbnail" src="" style="width: 100%">
                    <h3 class="mt-4" id="modal-promotion-title" style="text-align: center"></h3>
                    <div class="mt-1" id="modal-promotion-context"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function open_promotion(id) {
            let promotion_id = '#promotion-' + id;
            let promotion_thumbnail_path = $(promotion_id + ' ' + '.thumbnail').attr('src');
            let promotion_title = $(promotion_id + ' ' + '.title').text();
            let promotion_context = $(promotion_id + ' ' + '.context').html();
            $('#modal-promotion-thumbnail').attr('src', promotion_thumbnail_path);
            $('#modal-promotion-title').text(promotion_title);
            $('#modal-promotion-context').html(promotion_context);
            $('#modal-promotion').modal('show');

        }
    </script>

@endsection