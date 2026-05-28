@if(isset($nextTour) && $nextTour)
<div id="announcement-bar" class="announcement-bar" style="background: var(--accent); color: white; padding: 12px 20px; text-align: center; position: sticky; top: 0; z-index: 999; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: center; gap: 15px; overflow: hidden;">
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
    function adjustHeaderTop() {
        var bar = document.getElementById('announcement-bar');
        var header = document.getElementById('header');
        if (bar && header) {
            header.style.top = bar.offsetHeight + 'px';
        }
    }
    document.addEventListener('DOMContentLoaded', adjustHeaderTop);
    window.addEventListener('resize', adjustHeaderTop);
    window.addEventListener('load', adjustHeaderTop);
</script>
@endif
