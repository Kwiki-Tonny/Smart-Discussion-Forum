<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Discussion Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { border-radius: 0px !important; font-family: 'Segoe UI', Inter, sans-serif; }
    </style>
</head>
<body class="bg-[#F9F9F9] flex items-center justify-center min-h-screen text-[#000000]">

    <div class="w-full max-w-md bg-white border border-[#E5E5E5] p-8 shadow-sm">
        <div class="mb-8 border-b border-[#000000] pb-4">
            <h1 class="text-xl font-bold uppercase tracking-wider">Smart Discussion Forum</h1>
            <p class="text-xs text-[#666666] mt-1">Web Client Workspace Portal</p>
        </div>

        <form action="#" method="POST" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Workspace Email</label>
                <input type="email" name="email" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Password</label>
                <input type="password" name="password" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full bg-white border-2 border-[#000000] py-2.5 text-sm font-bold uppercase tracking-wider text-[#000000] hover:bg-[#F5F5F5] active:bg-[#E5E5E5] transition-colors">
                    Access Workspace
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="#" class="text-xs text-[#666666] hover:text-[#000000] underline">Trouble logging in? Contact Administrator</a>
        </div>
    </div>

</body>
</html>