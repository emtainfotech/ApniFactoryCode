import os
import shutil
from PIL import Image, ImageDraw, ImageFont

brands = [
    {
        "filename": "badge_creation.png",
        "name": "Creation",
        "title": "CREATION",
        "sub": "INDUSTRIAL PAINTS",
        "bg_top": (15, 32, 66),
        "bg_bottom": (30, 58, 138),
        "ring": (234, 179, 8),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_autocoats.png",
        "name": "Autocoats",
        "title": "AUTOCOATS",
        "sub": "AUTOMOTIVE FINISH",
        "bg_top": (131, 24, 67),
        "bg_bottom": (185, 28, 28),
        "ring": (251, 191, 36),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_autocoats_enamel.png",
        "name": "Autocoats Enamel",
        "title": "AUTOCOATS",
        "sub": "PU ENAMEL",
        "bg_top": (127, 29, 29),
        "bg_bottom": (185, 28, 28),
        "ring": (254, 240, 138),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_shrirang.png",
        "name": "Shrirang",
        "title": "SHRIRANG",
        "sub": "PAINTS & PRIMERS",
        "bg_top": (180, 83, 9),
        "bg_bottom": (217, 119, 6),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_bright.png",
        "name": "Bright",
        "title": "BRIGHT",
        "sub": "SYNTHETIC PAINTS",
        "bg_top": (6, 95, 70),
        "bg_bottom": (5, 150, 105),
        "ring": (254, 243, 199),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_bright_auto_paint.png",
        "name": "Bright Auto Paint",
        "title": "BRIGHT",
        "sub": "AUTO PAINT",
        "bg_top": (15, 118, 110),
        "bg_bottom": (13, 148, 136),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_asian_paints_pro.png",
        "name": "Asian Paints Pro",
        "title": "ASIAN PAINTS",
        "sub": "PRO FINISH",
        "bg_top": (153, 27, 27),
        "bg_bottom": (220, 38, 38),
        "ring": (253, 224, 71),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_berger_colorcraft.png",
        "name": "Berger ColorCraft",
        "title": "BERGER",
        "sub": "COLORCRAFT",
        "bg_top": (30, 64, 175),
        "bg_bottom": (37, 99, 235),
        "ring": (254, 202, 202),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_ultrashield.png",
        "name": "UltraShield Extreme Armor",
        "title": "ULTRASHIELD",
        "sub": "EXTREME ARMOR",
        "bg_top": (24, 24, 27),
        "bg_bottom": (39, 39, 42),
        "ring": (234, 88, 12),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_parijat.png",
        "name": "Parijat",
        "title": "PARIJAT",
        "sub": "SUPER PAINTS",
        "bg_top": (88, 28, 135),
        "bg_bottom": (124, 58, 237),
        "ring": (254, 240, 138),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_mastercraft.png",
        "name": "MasterCraft Tools",
        "title": "MASTERCRAFT",
        "sub": "PAINT TOOLS",
        "bg_top": (51, 65, 85),
        "bg_bottom": (71, 85, 105),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_factorystandard.png",
        "name": "FactoryStandard",
        "title": "FACTORY",
        "sub": "STANDARD",
        "bg_top": (55, 48, 163),
        "bg_bottom": (79, 70, 229),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_creation_gold.png",
        "name": "Creation Gold Luxury Silk Emulsion Base",
        "title": "CREATION",
        "sub": "GOLD LUXURY",
        "bg_top": (146, 64, 14),
        "bg_bottom": (180, 83, 9),
        "ring": (254, 240, 138),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_raymond.png",
        "name": "Raymond Apparel",
        "title": "RAYMOND",
        "sub": "APPAREL",
        "bg_top": (136, 19, 55),
        "bg_bottom": (190, 18, 60),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_vardhman.png",
        "name": "Vardhman Textiles",
        "title": "VARDHMAN",
        "sub": "TEXTILES",
        "bg_top": (20, 83, 45),
        "bg_bottom": (22, 101, 52),
        "ring": (255, 255, 255),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_mysoresilk.png",
        "name": "Mysore Silk Works",
        "title": "MYSORE SILK",
        "sub": "HERITAGE",
        "bg_top": (88, 28, 135),
        "bg_bottom": (109, 40, 217),
        "ring": (253, 224, 71),
        "text_color": (255, 255, 255)
    },
    {
        "filename": "badge_fabindia.png",
        "name": "FabIndia Crafts",
        "title": "FABINDIA",
        "sub": "CRAFTS",
        "bg_top": (154, 52, 18),
        "bg_bottom": (194, 65, 12),
        "ring": (254, 240, 138),
        "text_color": (255, 255, 255)
    }
]

dest1 = r"c:\Users\EPC001\Desktop\ApniFactoryBackend\public\storage\brand"
dest2 = r"c:\Users\EPC001\Desktop\ApniFactoryBackend\storage\app\public\brand"
os.makedirs(dest1, exist_ok=True)
os.makedirs(dest2, exist_ok=True)

size = (512, 512)

font_title = None
font_sub = None
font_initial = None

font_candidates = [
    r"C:\Windows\Fonts\arialbd.ttf",
    r"C:\Windows\Fonts\seguisb.ttf",
    r"C:\Windows\Fonts\calibrib.ttf",
    r"C:\Windows\Fonts\arial.ttf"
]

for fc in font_candidates:
    if os.path.exists(fc):
        try:
            font_title = ImageFont.truetype(fc, 38)
            font_sub = ImageFont.truetype(fc, 20)
            font_initial = ImageFont.truetype(fc, 110)
            break
        except Exception:
            pass

if font_title is None:
    font_title = ImageFont.load_default()
    font_sub = ImageFont.load_default()
    font_initial = ImageFont.load_default()

for b in brands:
    img = Image.new("RGBA", size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)

    cx, cy = size[0] // 2, size[1] // 2
    radius = 240

    # Draw gradient circular background
    for r in range(radius, 0, -1):
        ratio = (radius - r) / radius
        r_c = int(b["bg_top"][0] * (1 - ratio) + b["bg_bottom"][0] * ratio)
        g_c = int(b["bg_top"][1] * (1 - ratio) + b["bg_bottom"][1] * ratio)
        b_c = int(b["bg_top"][2] * (1 - ratio) + b["bg_bottom"][2] * ratio)
        draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=(r_c, g_c, b_c, 255))

    # Outer decorative ring
    draw.ellipse([cx - radius, cy - radius, cx + radius, cy + radius], outline=b["ring"], width=8)
    draw.ellipse([cx - (radius - 14), cy - (radius - 14), cx + (radius - 14), cy + (radius - 14)], outline=b["ring"], width=2)

    # Initial emblem in upper middle
    initial_char = b["title"][0]
    bbox_init = draw.textbbox((0, 0), initial_char, font=font_initial)
    iw = bbox_init[2] - bbox_init[0]
    draw.text((cx - iw // 2, cy - 130), initial_char, fill=b["ring"], font=font_initial)

    # Title text
    bbox_t = draw.textbbox((0, 0), b["title"], font=font_title)
    tw = bbox_t[2] - bbox_t[0]
    draw.text((cx - tw // 2, cy + 30), b["title"], fill=b["text_color"], font=font_title)

    # Divider bar
    draw.line([cx - 90, cy + 85, cx + 90, cy + 85], fill=b["ring"], width=3)

    # Subtitle text
    bbox_s = draw.textbbox((0, 0), b["sub"], font=font_sub)
    sw = bbox_s[2] - bbox_s[0]
    draw.text((cx - sw // 2, cy + 105), b["sub"], fill=b["ring"], font=font_sub)

    out1 = os.path.join(dest1, b["filename"])
    out2 = os.path.join(dest2, b["filename"])
    img.save(out1, "PNG")
    try:
        shutil.copy2(out1, out2)
    except Exception:
        img.save(out2, "PNG")
    print(f"Generated brand logo: {b['filename']}")

print("All brand badges generated successfully!")
