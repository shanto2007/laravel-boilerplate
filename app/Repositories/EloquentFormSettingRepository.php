<?php

namespace App\Repositories;

use App\Exceptions\GeneralException;
use App\Models\FormSetting;
use App\Repositories\Contracts\FormSettingRepository;
use App\Repositories\Traits\HtmlActionsButtons;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class EloquentFormSettingRepository.
 */
class EloquentFormSettingRepository extends BaseRepository implements FormSettingRepository
{
    use HtmlActionsButtons;

    /**
     * EloquentFormSettingRepository constructor.
     *
     * @param FormSetting $formSetting
     */
    public function __construct(FormSetting $formSetting)
    {
        parent::__construct($formSetting);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->query()->select([
            'id',
            'name',
            'recipients',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @param $name
     *
     * @return FormSetting
     */
    public function find($name)
    {
        /* @var FormSetting $formSetting */
        return $this->query()->whereName($name)->first();
    }

    /**
     * @param array $input
     *
     * @return \App\Models\FormSetting
     *
     * @throws \Exception|\Throwable
     */
    public function store(array $input)
    {
        /** @var FormSetting $formSetting */
        $formSetting = $this->make($input);

        if ($this->find($formSetting->name)) {
            throw new GeneralException(trans('exceptions.backend.form_settings.already_exist'));
        }

        DB::transaction(function () use ($formSetting) {
            if ($formSetting->save()) {
                return true;
            }

            throw new GeneralException(trans('exceptions.backend.form_settings.create'));
        });

        return $formSetting;
    }

    /**
     * @param FormSetting $formSetting
     * @param array       $input
     *
     * @return \App\Models\FormSetting
     *
     * @throws Exception
     * @throws \Exception|\Throwable
     */
    public function update(FormSetting $formSetting, array $input)
    {
        if (($existingFormSetting = $this->find($formSetting->name))
            && $existingFormSetting->id !== $formSetting->id
        ) {
            throw new GeneralException(trans('exceptions.backend.form_settings.already_exist'));
        }

        DB::transaction(function () use ($formSetting, $input) {
            if ($formSetting->update($input)) {
                $formSetting->save();

                return true;
            }

            throw new GeneralException(trans('exceptions.backend.form_settings.update'));
        });

        return $formSetting;
    }

    /**
     * @param FormSetting $formSetting
     *
     * @return bool|null
     *
     * @throws \Exception|\Throwable
     */
    public function destroy(FormSetting $formSetting)
    {
        DB::transaction(function () use ($formSetting) {
            if ($formSetting->delete()) {
                return true;
            }

            throw new GeneralException(trans('exceptions.backend.form_settings.delete'));
        });

        return true;
    }

    /**
     * @param \App\Models\FormSetting $formSetting
     *
     * @return mixed
     */
    public function getActionButtons(FormSetting $formSetting)
    {
        $buttons = $this->getEditButtonHtml('admin.form_setting.edit', $formSetting)
            .$this->getDeleteButtonHtml('admin.form_setting.destroy', $formSetting);

        return $buttons;
    }
}