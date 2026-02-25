<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName($pageTitle ?? '404 | page not found') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <!-- bootstrap 4  -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <!-- dashdoard main css -->
    <link rel="stylesheet" href="{{ asset('assets/errors/css/main.css') }}">
    <style>
        body {
            background-color: #1a1a2e;
            margin: 0;
            padding: 0;
        }
        .error {
            background-color: #1a1a2e !important;
            background-image: url({{ asset('assets/errors/images/bg-404.png') }});
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            min-height: 100vh;
        }
        .bot-message {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ff6b6b;
            backdrop-filter: blur(10px);
        }
        .funny-text {
            font-size: 1.2em;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .emoji {
            font-size: 2em;
            margin: 10px;
        }
        .stats-box {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .error .title {
            color: #fff !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .error .description {
            color: #fff !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
    </style>
</head>

<body>
    <!-- error-404 start -->
    <div class="error" style="background-image: url({{ asset('assets/errors/images/bg-404.png') }})">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-8 text-center">
                    <div class="emoji">🤖</div>
                    <h2 class="title" style="margin-top: 20px;">Oops! That Page Doesn't Exist</h2>
                    
                    <div class="bot-message">
                        <p class="funny-text">
                            <strong>Nice try, bot!</strong> 🕵️‍♂️
                        </p>
                        <p class="description" style="margin-top: 15px;">
                            We see you're looking for something that doesn't exist. Perhaps 
                            you're just lost in cyberspace. Either way, this page 
                            isn't here, and it never will be! 🎭
                        </p>
                    </div>

                    <div class="stats-box">
                        <p style="margin: 0;">
                            <strong>Fun Fact:</strong> You're visitor #<span id="random-number"></span> 
                            trying to access this non-existent page today! 
                            <span class="emoji" style="font-size: 1em;">📊</span>
                        </p>
                    </div>

                    <div class="bot-message" style="border-left-color: #4ecdc4;">
                        <p class="description">
                            <strong>For the bots out there:</strong> 👋<br>
                            Keep scanning, but you won't find what you're looking for here. 
                            This is like trying to find a needle in a haystack, except 
                            the haystack is empty and the needle was never there! 🎪
                        </p>
                    </div>

                    <div class="bot-message" style="border-left-color: #ffe66d;">
                        <p class="description">
                            <strong>For humans:</strong> 👨‍💻<br>
                            If you're a real person who got here by accident, don't worry! 
                            We've got your back. Just click the button below to go home. 
                            No judgment here! 😊
                        </p>
                    </div>

                    <a href="{{ url('/') }}" class="cmn-btn mt-4">
                        <span class="icon">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.0861 7.20091L21.6339 6.00578V2.05961C21.6461 1.91445 21.6283 1.76832 21.5816 1.63047C21.5349 1.49261 21.4604 1.36604 21.3627 1.25877C21.2651 1.1515 21.1464 1.06586 21.0143 1.00728C20.8821 0.948694 20.7394 0.918445 20.595 0.918445C20.4507 0.918445 20.3079 0.948694 20.1758 1.00728C20.0436 1.06586 19.925 1.1515 19.8273 1.25877C19.7296 1.36604 19.6551 1.49261 19.6084 1.63047C19.5618 1.76832 19.544 1.91445 19.5562 2.05961V4.26195L15.7954 1.1877C14.8643 0.419599 13.6988 0 12.4963 0C11.2938 0 10.1283 0.419599 9.1972 1.1877L1.90647 7.20091C1.31034 7.68908 0.829803 8.30551 0.499961 9.00515C0.170118 9.7048 -0.000694968 10.47 2.12507e-06 11.2448V19.7384C2.12507e-06 21.1339 0.549226 22.4722 1.52685 23.4589C2.50448 24.4457 3.83042 25 5.21299 25H19.787C21.1696 25 22.4955 24.4457 23.4731 23.4589C24.4508 22.4722 25 21.1339 25 19.7384V11.2147C24.9954 10.4444 24.8213 9.6847 24.4903 8.99056C24.1593 8.29642 23.6797 7.68514 23.0861 7.20091ZM9.37593 22.8578V15.5818C9.41511 14.7722 9.76135 14.0088 10.3429 13.4497C10.9245 12.8907 11.6969 12.5789 12.5 12.5789C13.3031 12.5789 14.0755 12.8907 14.6571 13.4497C15.2387 14.0088 15.5849 14.7722 15.6241 15.5818V22.8578H9.37593ZM22.9223 19.7084C22.9203 20.543 22.5909 21.343 22.0062 21.9332C21.4214 22.5234 20.6289 22.8558 19.8019 22.8578H17.7093V15.5818C17.7093 14.1864 17.16 12.8481 16.1824 11.8613C15.2048 10.8746 13.8788 10.3203 12.4963 10.3203C11.1137 10.3203 9.78776 10.8746 8.81014 11.8613C7.83251 12.8481 7.28329 14.1864 7.28329 15.5818V22.8578H5.1981C4.36984 22.8578 3.5754 22.5262 2.98904 21.9358C2.40268 21.3454 2.07227 20.5443 2.0703 19.7084V11.2147C2.06981 10.7471 2.17248 10.2853 2.37085 9.86278C2.56922 9.44024 2.85832 9.06758 3.21716 8.77186L10.5079 2.75865C11.0662 2.30218 11.7629 2.0531 12.4814 2.0531C13.1999 2.0531 13.8966 2.30218 14.4549 2.75865L21.7456 8.77186C22.1099 9.06462 22.4047 9.43597 22.6083 9.85868C22.8119 10.2814 22.9192 10.7447 22.9223 11.2147V19.7084Z" fill="white"/>
                            </svg>
                        </span>
                        <span class="text">Go to Home</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- error-404 end -->
    <script>
        // Generate a random number for the "visitor" count
        document.getElementById('random-number').textContent = Math.floor(Math.random() * 9999) + 1000;
    </script>
</body>

</html>
