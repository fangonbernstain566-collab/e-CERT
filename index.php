<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event_name; ?> - Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --cit-blue: #003366; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background-image: url('assets/pattern.png'); 
            background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;
            display: flex; flex-direction: column; align-items: center; padding: 40px 20px; margin: 0; min-height: 100vh;
        }
        .form-container { 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);
            padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            width: 100%; max-width: 450px; text-align: center; border-top: 8px solid var(--cit-blue);
        }
        input { width: 100%; padding: 14px; margin: 10px 0; border: 2px solid #eee; border-radius: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: var(--cit-blue); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        button:hover { background: #002244; transform: translateY(-2px); }
        
        /* THE PREVIEW AREA */
        .cert-preview-area { 
            position: relative; 
            width: 800px; 
            height: 566px; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); 
            border: 4px solid white; 
            margin-top: 20px; 
            background: #eee;
        }
        .cert-bg { width: 100%; height: 100%; object-fit: cover; }
        
        .preview-name { 
            position: absolute; 
            top: 36%; /* Adjusted to center on the blue template line */
            left: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 38px; 
            font-weight: 800; 
            color: black; /* White text for the blue template */
            text-transform: uppercase; 
            z-index: 5; 
        }

        .signature-protector {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 130px; 
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .protector-text {
            color: white;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.3);
            opacity: 0.8;
        }
        
        #messageBox { display: none; position: fixed; top: 30px; left: 50%; transform: translateX(-50%); padding: 15px 30px; border-radius: 50px; color: white; font-weight: 600; z-index: 1000; animation: fadeAndSlide 5s ease-in-out forwards; }
        @keyframes fadeAndSlide { 0% { top: -100px; opacity: 0; } 15% { top: 30px; opacity: 1; } 85% { top: 30px; opacity: 1; } 100% { top: 0px; opacity: 0; } }
        .success-bg { background: #27ae60; }
        .error-bg { background: #e74c3c; }

    </style>
</head>
<body>
    <div id="messageBox"></div>
    <div class="form-container">
        <h2><?php echo $event_name; ?></h2> 
        <p>Complete the form to generate your official certificate.</p>
        <form action="process.php" method="POST" id="certForm">
            <input type="text" id="nameInput" name="name" placeholder="Full Name" required autocomplete="off">
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit" id="submitBtn">Generate & Send Certificate</button>
        </form>
    </div>
    
    <h3 style="color: var(--cit-blue); margin-top: 40px;">Live Preview</h3>
    
    <div class="cert-preview-area">
        <img src="assets/CIT(1).png?v=<?php echo time(); ?>" class="cert-bg">
        
        <div id="nameDisplay" class="preview-name">YOUR NAME HERE</div>

        <div class="signature-protector">
            <div class="protector-text">Official Signatures Hidden in Preview</div>
        </div>
    </div>

    <script>
        document.getElementById('nameInput').addEventListener('input', function() {
            document.getElementById('nameDisplay').textContent = this.value.toUpperCase() || "YOUR NAME HERE";
        });

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status) {
                const msgBox = document.getElementById('messageBox');
                msgBox.textContent = status === 'success' ? "✅ Certificate sent successfully!" : "❌ Error: " + urlParams.get('message');
                msgBox.className = status === 'success' ? "success-bg" : "error-bg";
                msgBox.style.display = "block";
                setTimeout(() => { 
                    window.history.replaceState({}, document.title, window.location.pathname); 
                }, 5000);
            }
        };
    </script>
</body>
</html>