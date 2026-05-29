import sys

file = "public/_next/static/chunks/app/page-99eb7cf209d661ce.js"
with open(file, "r", encoding="utf-8") as f:
    content = f.read()

target = '(0,i.jsx)("div",{className:"relative z-10",children:(0,i.jsxs)("a",{href:"https://wa.me/2348032775756",target:"_blank",className:"bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2",children:[(0,i.jsx)(a.Icon,{icon:"ion:logo-whatsapp"})," Chat on WhatsApp"]})})'

rep = '(0,i.jsxs)("div",{className:"relative z-10 flex flex-col sm:flex-row gap-4",children:[(0,i.jsxs)("a",{href:"https://wa.me/2348032775756",target:"_blank",className:"bg-white text-primary px-10 py-4 rounded-full font-bold text-xl hover:shadow-xl transition-all inline-flex items-center gap-2",children:[(0,i.jsx)(a.Icon,{icon:"ion:logo-whatsapp"})," Chat on WhatsApp"]}),(0,i.jsx)("a",{href:"/signup",className:"bg-transparent border-2 border-white text-white px-10 py-4 rounded-full font-bold text-xl hover:bg-white hover:text-primary transition-all inline-flex items-center justify-center",children:"Sign Up Now"})]})'

if target in content:
    content = content.replace(target, rep)
    with open(file, "w", encoding="utf-8") as f:
        f.write(content)
    print("Fixed page.js successfully!")
else:
    print("Target not found in page.js!")
