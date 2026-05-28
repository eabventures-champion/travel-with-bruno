const fs = require('fs');
const file = 'resources/views/partials/tourism-modals.blade.php';
let content = fs.readFileSync(file, 'utf8');

// Normalize line endings to avoid \r\n vs \n issues
content = content.replace(/\r\n/g, '\n');

const target1 = "                            <template x-if=\"bookingType === 'tourism'\">\n                                <div style=\"grid-column: 1 / -1;\">";
const replacement1 = "                            <template x-if=\"bookingType === 'tourism'\">\n                                <div style=\"display: contents;\">\n                                    <div style=\"grid-column: 1 / -1;\">";

const target2 = `                                </div>
                                <div x-show="bookingItem?.package_type === 'fixed'" style="grid-column: 1 / -1; margin-top: 15px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Select Tour Schedule Date & Time <span style="color: var(--danger);">*</span></label>
                                    <input type="datetime-local" name="scheduled_at" :required="bookingItem?.package_type === 'fixed'"
                                           :min="minScheduleDate"
                                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Tours must be scheduled at least 7 days after the date of booking.</p>
                                </div>
                            </template>`;

const replacement2 = `                                </div>
                                <div x-show="bookingItem?.package_type === 'fixed'" style="grid-column: 1 / -1; margin-top: 15px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Select Tour Schedule Date & Time <span style="color: var(--danger);">*</span></label>
                                    <input type="datetime-local" name="scheduled_at" :required="bookingItem?.package_type === 'fixed'"
                                           :min="minScheduleDate"
                                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Tours must be scheduled at least 7 days after the date of booking.</p>
                                </div>
                                </div>
                            </template>`;

if (content.includes(target1) && content.includes(target2)) {
    content = content.replace(target1, replacement1);
    content = content.replace(target2, replacement2);
    fs.writeFileSync(file, content, 'utf8');
    console.log('SUCCESS');
} else {
    console.log('FAILED');
    console.log('has target1:', content.includes(target1));
    console.log('has target2:', content.includes(target2));
}
