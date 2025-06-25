import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
    RichText,
    BlockControls,
    AlignmentToolbar
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    RangeControl,
    ColorPalette,
    TextControl
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import React from 'react';

registerBlockType('wp-plugin-starter/example-block', {
    edit: function Edit({ attributes, setAttributes }) {
        const {
            text,
            displayType,
            headingLevel,
            typography,
            colors,
            spacing,
            alignment
        } = attributes;

        const blockProps = useBlockProps({
            className: `wps-block ${alignment ? `has-text-align-${alignment}` : ''}`
        });

        const updateTypography = (property, value) => {
            setAttributes({
                typography: {
                    ...typography,
                    [property]: value
                }
            });
        };

        const updateColors = (property, value) => {
            setAttributes({
                colors: {
                    ...colors,
                    [property]: value
                }
            });
        };

        const updateSpacing = (property, value) => {
            setAttributes({
                spacing: {
                    ...spacing,
                    [property]: value
                }
            });
        };

        // Build inline styles for preview
        const previewStyles = {};
        if (typography.fontSize) previewStyles.fontSize = `${typography.fontSize}px`;
        if (typography.fontWeight) previewStyles.fontWeight = typography.fontWeight;
        if (typography.lineHeight) previewStyles.lineHeight = typography.lineHeight;
        if (typography.letterSpacing) previewStyles.letterSpacing = `${typography.letterSpacing}px`;
        if (colors.textColor) previewStyles.color = colors.textColor;
        if (colors.backgroundColor) previewStyles.backgroundColor = colors.backgroundColor;
        if (spacing.marginTop) previewStyles.marginTop = `${spacing.marginTop}px`;
        if (spacing.marginBottom) previewStyles.marginBottom = `${spacing.marginBottom}px`;
        if (spacing.paddingTop) previewStyles.paddingTop = `${spacing.paddingTop}px`;
        if (spacing.paddingBottom) previewStyles.paddingBottom = `${spacing.paddingBottom}px`;

        const renderPreview = () => {
            const content = displayType === 'heading' ?
                React.createElement(`h${headingLevel || 2}`, { style: previewStyles }, text) :
                <p style={previewStyles}>{text}</p>;

            return (
                <div style={{ position: 'relative' }}>
                    {content}
                    <div style={{
                        position: 'absolute',
                        top: '-10px',
                        right: '-10px',
                        background: '#fff',
                        border: '1px solid #ddd',
                        borderRadius: '4px',
                        padding: '5px',
                        boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
                        zIndex: 10,
                        fontSize: '12px',
                        color: '#666'
                    }}>
                        WordPress Plugin Starter
                    </div>
                </div>
            );
        };

        return (
            <>
                <BlockControls>
                    <AlignmentToolbar
                        value={alignment}
                        onChange={(newAlignment) => setAttributes({ alignment: newAlignment })}
                    />
                </BlockControls>

                <InspectorControls>
                    <PanelBody title={__('Block Settings', 'wp-plugin-starter')} initialOpen={true}>
                        <TextControl
                            label={__('Text Content', 'wp-plugin-starter')}
                            value={text}
                            onChange={(value) => setAttributes({ text: value })}
                        />

                        <SelectControl
                            label={__('Display Type', 'wp-plugin-starter')}
                            value={displayType}
                            options={[
                                { label: __('Paragraph', 'wp-plugin-starter'), value: 'paragraph' },
                                { label: __('Heading', 'wp-plugin-starter'), value: 'heading' }
                            ]}
                            onChange={(value) => setAttributes({ displayType: value })}
                        />

                        {displayType === 'heading' && (
                            <SelectControl
                                label={__('Heading Level', 'wp-plugin-starter')}
                                value={headingLevel || 2}
                                options={[
                                    { label: __('H1 - Main Heading', 'wp-plugin-starter'), value: 1 },
                                    { label: __('H2 - Sub Heading', 'wp-plugin-starter'), value: 2 },
                                    { label: __('H3 - Sub Sub Heading', 'wp-plugin-starter'), value: 3 },
                                    { label: __('H4', 'wp-plugin-starter'), value: 4 },
                                    { label: __('H5', 'wp-plugin-starter'), value: 5 },
                                    { label: __('H6', 'wp-plugin-starter'), value: 6 }
                                ]}
                                onChange={(value) => setAttributes({ headingLevel: parseInt(value) })}
                            />
                        )}
                    </PanelBody>

                    <PanelBody title={__('Typography', 'wp-plugin-starter')} initialOpen={false}>
                        <RangeControl
                            label={__('Font Size (px)', 'wp-plugin-starter')}
                            value={typography.fontSize}
                            onChange={(value) => updateTypography('fontSize', value)}
                            min={12}
                            max={72}
                            step={1}
                        />

                        <SelectControl
                            label={__('Font Weight', 'wp-plugin-starter')}
                            value={typography.fontWeight}
                            options={[
                                { label: __('Normal', 'wp-plugin-starter'), value: 'normal' },
                                { label: __('Bold', 'wp-plugin-starter'), value: 'bold' },
                                { label: __('100', 'wp-plugin-starter'), value: '100' },
                                { label: __('200', 'wp-plugin-starter'), value: '200' },
                                { label: __('300', 'wp-plugin-starter'), value: '300' },
                                { label: __('400', 'wp-plugin-starter'), value: '400' },
                                { label: __('500', 'wp-plugin-starter'), value: '500' },
                                { label: __('600', 'wp-plugin-starter'), value: '600' },
                                { label: __('700', 'wp-plugin-starter'), value: '700' },
                                { label: __('800', 'wp-plugin-starter'), value: '800' },
                                { label: __('900', 'wp-plugin-starter'), value: '900' }
                            ]}
                            onChange={(value) => updateTypography('fontWeight', value)}
                        />

                        <RangeControl
                            label={__('Line Height', 'wp-plugin-starter')}
                            value={typography.lineHeight}
                            onChange={(value) => updateTypography('lineHeight', value)}
                            min={1}
                            max={3}
                            step={0.1}
                        />

                        <RangeControl
                            label={__('Letter Spacing (px)', 'wp-plugin-starter')}
                            value={typography.letterSpacing}
                            onChange={(value) => updateTypography('letterSpacing', value)}
                            min={-2}
                            max={10}
                            step={0.1}
                        />
                    </PanelBody>

                    <PanelBody title={__('Colors', 'wp-plugin-starter')} initialOpen={false}>
                        <div>
                            <label>{__('Text Color', 'wp-plugin-starter')}</label>
                            <ColorPalette
                                value={colors.textColor}
                                onChange={(value) => updateColors('textColor', value)}
                            />
                        </div>

                        <div style={{ marginTop: '20px' }}>
                            <label>{__('Background Color', 'wp-plugin-starter')}</label>
                            <ColorPalette
                                value={colors.backgroundColor}
                                onChange={(value) => updateColors('backgroundColor', value)}
                            />
                        </div>
                    </PanelBody>

                    <PanelBody title={__('Spacing', 'wp-plugin-starter')} initialOpen={false}>
                        <RangeControl
                            label={__('Margin Top (px)', 'wp-plugin-starter')}
                            value={spacing.marginTop}
                            onChange={(value) => updateSpacing('marginTop', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Margin Bottom (px)', 'wp-plugin-starter')}
                            value={spacing.marginBottom}
                            onChange={(value) => updateSpacing('marginBottom', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Padding Top (px)', 'wp-plugin-starter')}
                            value={spacing.paddingTop}
                            onChange={(value) => updateSpacing('paddingTop', value)}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <RangeControl
                            label={__('Padding Bottom (px)', 'wp-plugin-starter')}
                            value={spacing.paddingBottom}
                            onChange={(value) => updateSpacing('paddingBottom', value)}
                            min={0}
                            max={100}
                            step={1}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {renderPreview()}
                </div>
            </>
        );
    },

    save: function Save() {
        // This block uses a PHP render callback, so we don't need to save anything
        return null;
    }
}); 