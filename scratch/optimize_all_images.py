import os
import shutil
from PIL import Image

dirs_to_process = [
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\public\storage\product",
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\storage\app\public\product",
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\public\storage\slider",
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\storage\app\public\slider",
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\public\storage\category",
    r"c:\Users\EPC001\Desktop\ApniFactoryBackend\storage\app\public\category"
]

total_orig = 0
total_new = 0
processed_count = 0

for d in dirs_to_process:
    if not os.path.exists(d):
        continue
    for fname in os.listdir(d):
        fpath = os.path.join(d, fname)
        if not os.path.isfile(fpath):
            continue
        ext = os.path.splitext(fname)[1].lower()
        if ext not in [".jpg", ".jpeg", ".png", ".webp"]:
            continue
        
        orig_sz = os.path.getsize(fpath)
        if orig_sz < 30000:  # already small (< 30KB)
            continue
            
        try:
            with Image.open(fpath) as im:
                # For sliders allow wider max width (800), for products/categories (500)
                max_w = 800 if "slider" in d else 500
                max_h = 500
                
                # Check if resize is needed
                w, h = im.size
                if w > max_w or h > max_h or orig_sz > 100000:
                    im.thumbnail((max_w, max_h), Image.Resampling.LANCZOS)
                    
                    tmp_out = fpath + ".tmp"
                    if ext in [".jpg", ".jpeg"]:
                        im.convert("RGB").save(tmp_out, "JPEG", quality=85, optimize=True)
                    elif ext == ".png":
                        # If image has no alpha, save as optimized PNG or JPEG
                        if im.mode in ("RGBA", "LA") or (im.mode == "P" and "transparency" in im.info):
                            im.save(tmp_out, "PNG", optimize=True)
                        else:
                            im.convert("RGB").save(tmp_out, "JPEG", quality=85, optimize=True)
                    elif ext == ".webp":
                        im.save(tmp_out, "WEBP", quality=85)
                    else:
                        continue
                    
                    new_sz = os.path.getsize(tmp_out)
                    if new_sz < orig_sz:
                        # Replace original
                        try:
                            os.replace(tmp_out, fpath)
                            total_orig += orig_sz
                            total_new += new_sz
                            processed_count += 1
                            print(f"Compressed {fname}: {orig_sz//1024}KB -> {new_sz//1024}KB")
                        except Exception as e:
                            if os.path.exists(tmp_out):
                                os.remove(tmp_out)
                    else:
                        if os.path.exists(tmp_out):
                            os.remove(tmp_out)
        except Exception as e:
            print(f"Error processing {fname}: {e}")

print(f"\nOptimization complete! Processed {processed_count} images.")
if total_orig > 0:
    print(f"Saved {(total_orig - total_new)//1024} KB ({100 - (total_new*100//total_orig)}% total reduction)!")

