<?php

/**
 * Валидатор проверяет является ли пользователь редактором или доктором с правом создания скидок
 *
 * @author nastya
 */
class Controller_Validator_CliOrEditorOrDiscountCreator extends Controller_Validator_Abstract
{
    /**
     * @inheritdoc
     */
    public function validate($params)
    {
        $params = array_values($params);
        $controller = $this->context->getController();
        $discountId = $controller->getInput()->receive($params[0]);
        $modelManager = $this->getService('modelManager');
        $accessAllowed = false;
        $user = $this->getService('user')->getCurrent();
        if ($user->key() >= 0 && $user->hasRole('editor')) {
            $accessAllowed = true;
        }
        $doctor = $user->getDoctor();
        if ($doctor && $doctor['discountCreator'] && $doctor['active']) {
            if ($discountId) {
                $discount = $modelManager->byKey('Clinic_Discount', $discountId);
                $discountClinicIds = $discount->getClinics()->column('id');
                $doctorClinicIds = $doctor->clinics()->column('id');
                $intersect = array_intersect($discountClinicIds, $doctorClinicIds);
                if ($intersect) {
                    $accessAllowed = true;
                }
            } else {
                $accessAllowed = true;
            }
        }
        if (!$accessAllowed) {
            return $this->accessDenied();
        }
    }
}
