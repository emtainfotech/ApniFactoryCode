import os
from PIL import Image

src = r"c:\Users\EPC001\Desktop\ApniFactoryBackend\public\storage\product\bulk_metallic_emulsion_paints.png"
orig_size = os.path.getsize(src)

im = Image.open(src)
im.thumbnail((500, 500), Image.Resampling.LANCZOS)
tmp_out = r"c:\Users\EPC001\Desktop\ApniFactoryBackend\scratch\test_opt.png"
im.save(tmp_out, "PNG", optimize=True)
new_size_png = os.path.getsize(tmp_out)

tmp_out_jpg = r"c:\Users\EPC001\Desktop\ApniFactoryBackend\scratch\test_opt.jpg"
im.convert("RGB").save(tmp_out_jpg, "JPEG", quality=85, optimize=True)
new_size_jpg = os.path.getsize(tmp_out_jpg)

print(f"Original: {orig_size:,} bytes")
print(f"Optimized PNG: {new_size_png:,} bytes ({(1 - new_size_png/orig_size)*100:.1f}% reduction)")
print(f"Optimized JPG: {new_size_jpg:,} bytes ({(1 - new_size_jpg/orig_size)*100:.1f}% reduction)")

