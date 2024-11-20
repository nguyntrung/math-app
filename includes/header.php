<div class="header-wrapper">
    <div class="container-fluid header-container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-left content-column">
                <div class="header-content" data-aos="fade-right">
                    <span class="badge">Website học toán cho học sinh lớp 5</span>
                    <h1 class="header-title">New Approach to Kids Education</h1>
                    <p class="header-description">
                        Sea ipsum kasd eirmod kasd magna, est sea et diam ipsum est amet sed sit.
                        Ipsum dolor no justo dolor et, lorem ut dolor erat dolore sed ipsum at ipsum nonumy amet. Clita
                        lorem dolore sed stet et est justo dolore.
                    </p>
                    <a href="#" class="cta-button">
                        <span>Tìm hiểu thêm</span>
                        <svg width="15" height="15" viewBox="0 0 15 15">
                            <path d="M8.293 3.293a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1 0 1.414l-3 3a1 1 0 0 1-1.414-1.414L10.586 7.5H3a1 1 0 1 1 0-2h7.586L8.293 4.707a1 1 0 0 1 0-1.414z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right image-column">
                <div class="header-image" data-aos="fade-left">
                    <img src="../assets/img/header.png" alt="Education illustration">
                </div>
            </div>
        </div>
    </div>

    <style>
        .header-wrapper {
            background: linear-gradient(135deg, #4A90E2 0%, #67B26F 100%);
            position: relative;
            overflow: hidden;
            padding: 60px 0;
        }

        .header-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .header-container {
            padding: 0 4%;
            max-width: 1400px;
        }

        .content-column {
            padding: 40px 15px;
        }

        .header-content {
            max-width: 540px;
            margin: 0 auto;
        }

        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .header-title {
            color: white;
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-description {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 32px;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            background: white;
            color: #4A90E2;
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: #2176FF;
        }

        .cta-button span {
            margin-right: 8px;
        }

        .cta-button svg {
            transition: transform 0.3s ease;
        }

        .cta-button:hover svg {
            transform: translateX(4px);
        }

        .header-image {
            position: relative;
            padding: 20px;
        }

        .header-image img {
            max-width: 100%;
            height: auto;
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.15));
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        @media (max-width: 991px) {
            .header-title {
                font-size: 2.5rem;
            }

            .content-column {
                text-align: center;
                padding: 40px 20px;
            }

            .header-content {
                margin: 0 auto;
            }

            .header-image {
                margin-top: 40px;
            }
        }

        @media (max-width: 576px) {
            .header-title {
                font-size: 2rem;
            }

            .header-description {
                font-size: 1rem;
            }

            .badge {
                font-size: 0.8rem;
            }

            .cta-button {
                padding: 12px 25px;
                font-size: 0.9rem;
            }
        }
    </style>
</div>