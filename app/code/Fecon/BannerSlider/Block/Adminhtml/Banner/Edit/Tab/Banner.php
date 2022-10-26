<?php

namespace Fecon\BannerSlider\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Render\Image as BannerImage;
use Mageplaza\BannerSlider\Helper\Data as HelperData;
use Mageplaza\BannerSlider\Helper\Image as HelperImage;

/**
 * Add new fields to Banner form
 */
class Banner extends \Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner
{

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\BannerSlider\Model\Banner $banner */
        $banner = $this->_coreRegistry->registry('mpbannerslider_banner');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('banner_');
        $form->setFieldNameSuffix('banner');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Banner Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($banner->getId()) {
            $fieldset->addField(
                'banner_id',
                'hidden',
                ['name' => 'banner_id']
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->statusOptions->toOptionArray(),
            ]
        );

        $typeBanner = $fieldset->addField(
            'type',
            'select',
            [
                'name'  => 'type',
                'label' => __('Type'),
                'title' => __('Type'),
                'values' => $this->typeOptions->toOptionArray(),
            ]
        );

        $uploadBanner = $fieldset->addField(
            'image',
            BannerImage::class,
            [
                'name' => 'image',
                'label' => __('Upload Image'),
                'title' => __('Upload Image'),
                'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_BANNER)
            ]
        );

        $titleBanner = $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Banner title'),
                'title' => __('Banner title'),
            ]
        );

        $urlBanner = $fieldset->addField(
            'url_banner',
            'text',
            [
                'name'  => 'url_banner',
                'label' => __('Url'),
                'title' => __('Url'),
            ]
        );

        $newtab = $fieldset->addField(
            'newtab',
            'select',
            [
                'name'  => 'newtab',
                'label' => __('Open new tab after click'),
                'title' => __('Open new tab after click'),
                'values' => $this->statusOptions->toOptionArray(),
                'note'   => __('Automatically open new tab after click on banner')

            ]
        );

        $subtitle = $fieldset->addField(
            'subtitle',
            'text',
            [
                'name'  => 'subtitle',
                'label' => __('Subheading'),
                'title' => __('Subheading'),
                'required' => false,
            ]
        );

        $imageDescription = $fieldset->addField(
            'image_description',
            'text',
            [
                'name'  => 'image_description',
                'label' => __('Content'),
                'title' => __('Content'),
                'required' => false,
            ]
        );

        $ctaText = $fieldset->addField(
            'cta_text',
            'text',
            [
                'name'  => 'cta_text',
                'label' => __('CTA Text'),
                'title' => __('CTA Text'),
                'required' => false,
            ]
        );

        $ctaLink = $fieldset->addField(
            'cta_link',
            'text',
            [
                'name'  => 'cta_link',
                'label' => __('CTA Link'),
                'title' => __('CTA Link'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'cta_target',
            'select',
            [
                'name'  => 'cta_target',
                'label' => __('CTA button open in new tab'),
                'title' => __('CTA button open in new tab'),
                'values' => $this->statusOptions->toOptionArray(),
                'note'   => __('Automatically open new tab after click on banners button')

            ]
        );

        $urlVideo = $fieldset->addField(
            'url_video',
            'text',
            [
                'name'  => 'url_video',
                'label' => __('Video Url'),
                'title' => __('Video Url'),
                'note'   => __('It supports Youtube video only. Just paste a Youtube video URL.')
            ]
        );

        if (!$banner->getId()) {
            $defaultImage = $this->getImageUrls() ? array_values($this->getImageUrls())[0] : '';
            $demotemplate = $fieldset->addField('default_template', 'select', [
                    'name'     => 'default_template',
                    'label'    => __('Demo template'),
                    'title'    => __('Demo template'),
                    'values'   => $this->template->toOptionArray(),
                    'note'     => '<img src="' . $defaultImage . '" alt="demo"  class="article_image" id="mp-demo-image">'
                ]
            );

            $fieldset->addField('images-urls', 'hidden', [
                    'name'  => 'image-urls',
                    'value' => HelperData::jsonEncode($this->getImageUrls())
                ]
            );

            $insertVariableButton = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button',
                '',
                [
                    'data' => [
                        'type'  => 'button',
                        'label' => __('Load Template'),
                    ]
                ]
            );
            $insertbutton = $fieldset->addField('load_template', 'note', [
                'text'  => $insertVariableButton->toHtml(),
                'label' => ''
            ]);
        }

        $content = $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'required'  => false,
                'config' => $this->_wysiwygConfig->getConfig(['hidden' => true,'add_variables' => false, 'add_widgets' => false, 'add_directives' => true])
            ]
        );

        $fieldset->addField('sliders_ids', '\Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Render\Slider', [
                'name' => 'sliders_ids',
                'label' => __('Sliders'),
                'title' => __('Sliders'),
            ]
        );

        if (!$banner->getSlidersIds()) {
            $banner->setSlidersIds($banner->getSliderIds());
        }

        $bannerData = $this->_session->getData('mpbannerslider_banner_data', true);
        if ($bannerData) {
            $banner->addData($bannerData);
        } else {
            if (!$banner->getId()) {
                $banner->addData($banner->getDefaultValues());
            }
        }

        $dependencies = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($typeBanner->getHtmlId(), $typeBanner->getName())
            ->addFieldMap($urlBanner->getHtmlId(), $urlBanner->getName())
            ->addFieldMap($uploadBanner->getHtmlId(), $uploadBanner->getName())
            ->addFieldMap($urlVideo->getHtmlId(), $urlVideo->getName())
            ->addFieldMap($titleBanner->getHtmlId(), $titleBanner->getName())
            ->addFieldMap($newtab->getHtmlId(), $newtab->getName())
            ->addFieldMap($content->getHtmlId(), $content->getName())
            ->addFieldMap($subtitle->getHtmlId(), $subtitle->getName())
            ->addFieldDependence($urlBanner->getName(),$typeBanner->getName(),'0')
            ->addFieldDependence($uploadBanner->getName(),$typeBanner->getName(),'0')
            ->addFieldDependence($titleBanner->getName(),$typeBanner->getName(),'0')
            ->addFieldDependence($newtab->getName(),$typeBanner->getName(),'0')
            ->addFieldDependence($content->getName(),$typeBanner->getName(),'2')
            ->addFieldDependence($urlVideo->getName(),$typeBanner->getName(),'1');

        if (!$banner->getId()) {
            $dependencies->addFieldMap($demotemplate->getHtmlId(), $demotemplate->getName())
            ->addFieldMap($insertbutton->getHtmlId(), $insertbutton->getName())
            ->addFieldDependence($demotemplate->getName(),$typeBanner->getName(),'2')
            ->addFieldDependence($insertbutton->getName(),$typeBanner->getName(),'2');
        }

        // define field dependencies
        $this->setChild('form_after', $dependencies);

        $form->addValues($banner->getData());
        $this->setForm($form);

        return $this;
    }
}
