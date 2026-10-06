document.addEventListener('DOMContentLoaded',()=>{
  const mainImage=document.getElementById('mainProductImage');
  document.querySelectorAll('[data-gallery-src]').forEach(button=>button.addEventListener('click',()=>{mainImage.src=button.dataset.gallerySrc;mainImage.alt=button.dataset.galleryAlt||'';document.querySelectorAll('[data-gallery-src]').forEach(item=>item.classList.remove('active'));button.classList.add('active');}));
  const dataElement=document.getElementById('variantData');
  if(dataElement){const variants=JSON.parse(dataElement.textContent||'[]');const price=document.getElementById('detailPrice');const stock=document.getElementById('detailStock');const quantity=document.getElementById('quantity');document.querySelectorAll('[data-variant-index]').forEach(button=>{const variant=variants[Number(button.dataset.variantIndex)];if(!variant||variant.stock<=0)button.classList.add('unavailable');button.addEventListener('click',()=>{document.querySelectorAll('[data-variant-index]').forEach(item=>item.classList.remove('active'));button.classList.add('active');price.innerHTML=`<strong>৳${formatPrice(variant.price)}</strong>${variant.price<variant.regular?`<del>৳${formatPrice(variant.regular)}</del>`:''}`;stock.className=`detail-stock ${variant.stock>0?'in':'out'}`;stock.textContent=variant.stock>0?'✓ স্টক আছে':'✕ স্টক শেষ';quantity.max=Math.max(1,variant.stock);document.getElementById('variantHelp').textContent=variant.weight?`${variant.weight} ${variant.unit}`:'';});});}
  document.querySelectorAll('[data-quantity]').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById('quantity');if(!input)return;const min=Number(input.min)||1,max=Number(input.max)||99,current=Number(input.value)||1;input.value=button.dataset.quantity==='plus'?Math.min(max,current+1):Math.max(min,current-1);}));
  const backToTop=document.querySelector('.back-to-top');
  if(backToTop){
    const updateBackToTop=()=>{const visible=window.scrollY>400;backToTop.classList.toggle('is-visible',visible);backToTop.setAttribute('aria-hidden',String(!visible));backToTop.tabIndex=visible?0:-1;};
    updateBackToTop();
    window.addEventListener('scroll',updateBackToTop,{passive:true});
    backToTop.addEventListener('click',()=>window.scrollTo({top:0,behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'}));
  }
});
function formatPrice(value){return new Intl.NumberFormat('bn-BD',{maximumFractionDigits:Number.isInteger(Number(value))?0:2}).format(value)}
