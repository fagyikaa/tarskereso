<?php

namespace Core\UserBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;

class RoleHandlingFrontEndAdapter {

    protected $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    /**
     * Recursively builds an associative array for the frontend bundle IVHTreeView. ROLE_SUPER_ADMIN is filtered out.
     * If $roles not empty, then every node with a role presented in $roles will be flagged with selected => true attribute, selected => false otherwise.
     * 
     * @param array $root
     * @param array|null $roles
     * @return array
     */
    public function getRoleTreeForFrontEnd($root, array $roles = null) {
        $IVHTreeView = array();
        foreach ($root as $subrole => $child) {
            if (count($child) > 0) {
                $IVHTreeView[] = array(
                    'label' => $this->translator->trans('role.' . $subrole, array(), 'role'),
                    'value' => $subrole,
                    'selected' => (!is_array($roles) ? false : in_array($subrole, $roles)),
                    'children' => $this->getRoleTreeForFrontEnd($child, $roles)
                );
            } else {
                $IVHTreeView[] = array(
                    'label' => $this->translator->trans('role.' . $subrole, array(), 'role'),
                    'value' => $subrole,
                    'selected' => !is_array($roles) ? false : in_array($subrole, $roles),
                );
            }
        }

        return $IVHTreeView;
    }

}
