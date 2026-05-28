@if(isset($nextTour) && $nextTour)
<div id="announcement-bar" class="announcement-bar" style="background: var(--accent); color: white; padding: 12px 20px; text-align: center; position: sticky; z-index: 998; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; gap: 15px; overflow: hidden;">
    <div class="flash-pulse" style="width: 10px; height: 10px; background: white; border-radius: 50%; box-shadow: 0 0 10px white;"></div>
    <span style="font-weight: 700; letter-spacing: 0.5px; font-size: 0.95rem;">
        NEXT BIG ADVENTURE: <span style="text-transform: uppercase;">{{ $nextTour->title }}</span> 
        — @if($nextTour->return_date) DATES @else DEPARTING @endif <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px;">{{ $nextTour->formatted_date_range }}</span>
    </span>
    <button @click="openBooking({{ json_encode($nextTour) }}, 'tourism')" class="pulse-btn" style="background: white; color: var(--accent); border: none; padding: 6px 15px; border-radius: 50px; font-weight: 800; font-size: 0.75rem; cursor: pointer; text-transform: uppercase; transition: transform 0.2s;">
        Secure Your Slot
    </button>
</div>
<style>
    .announcement-bar { animation: slideDown 0.5s ease-out; }
    @keyframes slideDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
    .flash-pulse { animation: pulseIcon 1.5s infinite; }
    @keyframes pulseIcon { 0% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.5); } 100% { opacity: 1; transform: scale(1); } }
    .pulse-btn { animation: pulseBtn 2s infinite; }
    @keyframes pulseBtn { 0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.7); } 70% { box-shadow: 0 0 0 10px rgba(255,255,255,0); } 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); } }
    @media (max-width: 768px) { .announcement-bar { flex-direction: column; gap: 8px; font-size: 0.8rem; } }
</style>
<script>
    function adjustHeaderAndBanner() {
        var header = document.getElementById('header');
        var bar = document.getElementById('announcement-bar');
        var mobileNav = document.querySelector('.mobile-nav');
        if (!header) return;
        
        if (!bar) {
            header.style.top = '0px';
            var headerHeight = header.offsetHeight;
            document.body.style.paddingTop = headerHeight + 'px';
            if (mobileNav) {
                mobileNav.style.top = headerHeight + 'px';
                mobileNav.style.height = 'calc(100vh - ' + headerHeight + 'px)';
            }
            return;
        }

        var isMobile = window.innerWidth <= 1024;
        
        // Reset dynamic styles first
        header.style.top = '';
        bar.style.top = '';
        bar.style.marginTop = '';
        bar.style.position = '';
        if (mobileNav) {
            mobileNav.style.top = '';
            mobileNav.style.height = '';
        }

        var headerHeight = header.offsetHeight;
        var barHeight = bar.offsetHeight;

        if (isMobile) {
            // Mobile: Header is fixed at the absolute top (top: 0)
            // Announcement banner sits fixed below it (top: headerHeight)
            header.style.top = '0px';
            bar.style.position = 'fixed';
            bar.style.width = '100%';
            bar.style.top = headerHeight + 'px';
            var totalTop = headerHeight + barHeight;
            document.body.style.paddingTop = totalTop + 'px';
            
            if (mobileNav) {
                mobileNav.style.top = totalTop + 'px';
                mobileNav.style.height = 'calc(100vh - ' + totalTop + 'px)';
            }
        } else {
            // Desktop: Announcement banner is fixed at the absolute top (top: 0)
            // Header sits fixed below it (top: barHeight)
            bar.style.position = 'fixed';
            bar.style.width = '100%';
            bar.style.top = '0px';
            header.style.top = barHeight + 'px';
            document.body.style.paddingTop = (headerHeight + barHeight) + 'px';
        }
    }
    document.addEventListener('DOMContentLoaded', adjustHeaderAndBanner);
    window.addEventListener('resize', adjustHeaderAndBanner);
    window.addEventListener('load', adjustHeaderAndBanner);
    window.addEventListener('scroll', adjustHeaderAndBanner);
</script>
@endif

