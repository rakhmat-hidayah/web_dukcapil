<?php

namespace App\Services\Profile;

use App\Models\Profile\ProfileSection;
use App\Models\Profile\ProfileSectionSetting;
use Illuminate\Support\Facades\Cache;

class ProfileBuilderService
{
    /**
     * Reorder sections.
     */
    public function reorderSections(array $sectionsOrder): void
    {
        foreach ($sectionsOrder as $index => $item) {
            ProfileSection::where('id', $item['id'])->update([
                'sort_order' => $index + 1,
                'is_enabled' => $item['is_enabled'] ?? true,
            ]);
        }
        Cache::forget('active_profile_sections');
    }

    /**
     * Update section settings.
     */
    public function updateSectionSetting(int $sectionId, array $data): ProfileSectionSetting
    {
        $setting = ProfileSectionSetting::firstOrCreate(
            ['section_id' => $sectionId],
            [
                'layout_type' => 'default',
                'bg_color' => 'transparent',
                'animation_type' => 'fade-up',
                'visible_desktop' => true,
                'visible_tablet' => true,
                'visible_mobile' => true,
            ]
        );

        $setting->update($data);
        Cache::forget('active_profile_sections');

        return $setting;
    }
}
