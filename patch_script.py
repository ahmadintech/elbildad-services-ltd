import sys

def patch_layout():
    file = "public/_next/static/chunks/app/layout-9c85db91db9fec5d.js"
    with open(file, "r", encoding="utf-8") as f:
        content = f.read()
    
    target1 = '16.6111 15.855Z"})})]}),(0,r.jsx)(l(),{href:"/rfq",className:"hidden lg:block bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-all",children:"Request Quote"})'
    rep1 = '16.6111 15.855Z"})})]}),(0,r.jsx)(l(),{href:"/signin",className:"hidden lg:block text-midnight_text dark:text-white font-bold hover:text-primary transition-all mr-6",children:"Sign In"}),(0,r.jsx)(l(),{href:"/rfq",className:"hidden lg:block bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-all",children:"Request Quote"})'
    content = content.replace(target1, rep1)

    target2 = 'w-full",children:(0,r.jsx)(l(),{href:"/rfq",className:"bg-primary text-white px-4 py-3 rounded-lg text-center font-bold",onClick:()=>w(!1),children:"Request Quote"})'
    rep2 = 'w-full",children:[(0,r.jsx)(l(),{href:"/signin",className:"border border-primary text-primary px-4 py-3 rounded-lg text-center font-bold hover:bg-primary hover:text-white transition-all",onClick:()=>w(!1),children:"Sign In"}),(0,r.jsx)(l(),{href:"/rfq",className:"bg-primary text-white px-4 py-3 rounded-lg text-center font-bold",onClick:()=>w(!1),children:"Request Quote"})]'
    content = content.replace(target2, rep2)
    
    with open(file, "w", encoding="utf-8") as f:
        f.write(content)
    print("Patched layout")

def patch_page():
    file = "public/_next/static/chunks/app/page-99eb7cf209d661ce.js"
    with open(file, "r", encoding="utf-8") as f:
        content = f.read()

    content = content.replace('Explore Services', 'Sign up / Register Now')
    content = content.replace('href:"#services",className:"btn_white', 'href:"/signup",className:"btn_white')
    content = content.replace('"10+"', '"5"')
    
    target = 'className:"relative z-10",children:(0,i.jsxs)("a",{href:"https://wa.me/2348032775756",target:"_blank",className:"bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2",children:[(0,i.jsx)("i",{className:"fa-brands fa-whatsapp text-2xl"})," Chat on WhatsApp"]})})'
    rep = 'className:"relative z-10 flex flex-col sm:flex-row gap-4",children:[(0,i.jsxs)("a",{href:"https://wa.me/2348032775756",target:"_blank",className:"bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2",children:[(0,i.jsx)("i",{className:"fa-brands fa-whatsapp text-2xl"})," Chat on WhatsApp"]}),(0,i.jsx)(d(),{href:"/signup",className:"bg-transparent border-2 border-white text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-white hover:text-primary transition-all inline-flex items-center justify-center",children:"Sign Up Now"})]})'
    # Fallback if d() is not the Link component import name, we can use a native <a> tag if needed, but d() is standard here
    # Actually wait! The chat on whatsapp uses <a>. I can just use <a> for signup too.
    rep = 'className:"relative z-10 flex flex-col sm:flex-row gap-4",children:[(0,i.jsxs)("a",{href:"https://wa.me/2348032775756",target:"_blank",className:"bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2",children:[(0,i.jsx)("i",{className:"fa-brands fa-whatsapp text-2xl"})," Chat on WhatsApp"]}),(0,i.jsx)("a",{href:"/signup",className:"bg-transparent border-2 border-white text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-white hover:text-primary transition-all inline-flex items-center justify-center",children:"Sign Up Now"})]})'
    content = content.replace(target, rep)
    
    with open(file, "w", encoding="utf-8") as f:
        f.write(content)
    print("Patched page.js")

def patch_home():
    file = "resources/views/home.blade.php"
    with open(file, "r", encoding="utf-8") as f:
        content = f.read()

    content = content.replace('Explore Services', 'Sign up / Register Now')
    content = content.replace('Register Now &rarr;', 'Sign up / Register Now &rarr;')
    content = content.replace('href="/#services"', 'href="/signup"')
    content = content.replace('10+', '5')
    
    target1 = '<a class="hidden lg:block bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-all" href="/rfq/">Request Quote</a>'
    rep1 = '<a class="hidden lg:block text-midnight_text dark:text-white font-bold hover:text-primary transition-all mr-6" href="/signin">Sign In</a>' + target1
    content = content.replace(target1, rep1)

    target2 = '<div class="mt-4 flex flex-col space-y-4 w-full"><a class="bg-primary text-white px-4 py-3 rounded-lg text-center font-bold" href="/rfq/">Request Quote</a>'
    rep2 = '<div class="mt-4 flex flex-col space-y-4 w-full"><a class="border border-primary text-primary px-4 py-3 rounded-lg text-center font-bold hover:bg-primary hover:text-white transition-all" href="/signin">Sign In</a><a class="bg-primary text-white px-4 py-3 rounded-lg text-center font-bold" href="/rfq/">Request Quote</a>'
    content = content.replace(target2, rep2)
    
    target3 = '<div class="relative z-10"><a href="https://wa.me/2348032775756" target="_blank" class="bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2"><span></span> Chat on WhatsApp</a></div>'
    rep3 = '<div class="relative z-10 flex flex-col sm:flex-row gap-4"><a href="https://wa.me/2348032775756" target="_blank" class="bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2"><span></span> Chat on WhatsApp</a><a href="/signup" class="bg-transparent border-2 border-white text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-white hover:text-primary transition-all inline-flex items-center justify-center">Sign Up Now</a></div>'
    content = content.replace(target3, rep3)
    
    with open(file, "w", encoding="utf-8") as f:
        f.write(content)
    print("Patched home.blade.php")

patch_layout()
patch_page()
patch_home()
