<template>
    <div slot="details" class="details">
        <detail-field v-for="(field, index) in getCustomFields" :key="index" :name="field.name" :type="field.type" :value="field.value"/>

        <translate tag="div" say="Statistics" class="header"/>
        <translate tag="div" say="Created on"><span>{{ getDateTime(password.created) }}</span></translate>
        <translate tag="div" say="Last updated"><span>{{ getDateTime(password.edited) }}</span></translate>
        <translate tag="div" say="Revisions">
            <translate say="{count} revisions" :variables="{count:countRevisions}"/>
        </translate>
        <translate tag="div" say="Shares">
            <translate say="{count} shares" :variables="{count:countShares}"/>
        </translate>

        <translate tag="div" say="Security" class="header"/>
        <translate tag="div" say="Status">
            <translate :say="getSecurityStatus" :class="getSecurityClass.toLowerCase()"/>
        </translate>
        <translate tag="div" say="Encryption">
            <translate :say="getEncryption" />
        </translate>
        <detail-field name="SHA-1" type="text" :value="password.hash"/>
    </div>
</template>

<script>
    import Web from '@vc/Web';
    import DetailField from '@vue/Details/Password/DetailField';
    import Translate from '@vc/Translate';
    import Localisation from '@js/Classes/Localisation';
    import SettingsManager from '@js/Manager/SettingsManager';

    export default {
        components: {
            Web,
            DetailField,
            Translate
        },

        props: {
            password: {
                type: Object
            }
        },

        data() {
            return {
                showHiddenFields: SettingsManager.get('client.ui.custom.fields.show.hidden')
            };
        },

        computed: {
            countShares() {
                let count = 0;
                for(let i in this.password.shares) {
                    if(this.password.shares.hasOwnProperty(i)) count++;
                }
                return count;
            },
            countRevisions() {
                let count = 0;
                for(let i in this.password.revisions) {
                    if(this.password.revisions.hasOwnProperty(i)) count++;
                }
                return count;
            },
            getSecurityClass() {
                let status = ['secure', 'weak', 'breached'];

                return status[this.password.status];
            },
            getSecurityStatus() {
                if(this.password.status === 1) return `Weak (${this.password.statusCode.toLowerCase().capitalize()})`;

                return this.getSecurityClass.capitalize();
            },
            getCustomFields() {
                let fields       = [],
                    customFields = this.password.customFields;

                fields.push({name: Localisation.translate('Name'), value: this.password.label});
                if(this.password.username) fields.push({name: Localisation.translate('Username'), value: this.password.username});
                fields.push({name: Localisation.translate('Password'), value: this.password.password, type: 'secret'});
                if(this.password.url) fields.push({name: Localisation.translate('Website'), value: this.password.url, type: 'url'});


                for(let name in customFields) {
                    if(!customFields.hasOwnProperty(name) || (!this.showHiddenFields && name.substr(0, 1) === '_')) continue;

                    fields.push(
                        {
                            name,
                            type : customFields[name].type,
                            value: customFields[name].value
                        }
                    );
                }

                return fields;
            },
            getEncryption() {
                let encryption = 'none';
                if(this.password.sseType === 'SSEv1r1') encryption = 'Server-side encryption';
                if(this.password.sseType === 'SSEv2r1') encryption = 'Advanced server-side encryption';
                if(this.password.cseType === 'CSEv1r1') encryption = 'Client-side encryption';

                return Localisation.translate(encryption)
            }
        },
        methods : {
            getDateTime(date) {
                return Localisation.formatDateTime(date);
            }
        }
    };
</script>

<style lang="scss">
    .item-details .details {
        padding-top : 10px;

        div:not(.header) {
            font-size     : 0.9em;
            font-style    : italic;
            margin-bottom : 5px;
            color         : var(--color-text-maxcontrast);

            a,
            span {
                display    : block;
                font-style : normal;
                font-size  : 1.3em;
                color      : var(--color-text-lighter);
                text-align : right;
                cursor     : text;
                word-wrap  : break-word;

                &.secret {
                    cursor : pointer;

                    &.visible {
                        font-family : var(--pw-mono-font-face);
                    }
                }

                &.secure {color : var(--color-success);}
                &.weak {color : var(--color-warning);}
                &.breached {color : var(--color-error);}
            }

            a {
                cursor : pointer;

                &:hover {
                    text-decoration : underline;
                }
            }
        }

        .header {
            margin-top  : 20px;
            font-size   : 1.3em;
            font-weight : bold;
            color       : var(--color-text-light);
        }
    }
</style>