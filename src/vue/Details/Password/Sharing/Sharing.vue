<template>
    <div class="sharing-container">
        <input type="text" v-model="search" class="share-add-user" :placeholder="placeholder" @keypress="submitAction($event)"/>
        <ul class="shares" v-if="shares.length !== 0">
            <share :share="share"
                   v-on:delete="deleteShare($event)"
                   v-on:update="refreshShares()"
                   :data-share-id="share.id"
                   v-for="share in shares"
                   :key="share.id"/>
        </ul>
        <ul class="user-search" v-if="matches.length !== 0">
            <li v-for="match in matches" @click="shareWithUser(match.id)">
                <img :src="getAvatarUrl(match.id)" alt="" class="avatar">&nbsp;{{match.name}}
            </li>
        </ul>
    </div>
</template>

<script>
    import API from '@js/Helper/api';
    import Translate from '@vc/Translate';
    import Messages from '@js/Classes/Messages';
    import Localisation from '@js/Classes/Localisation';
    import Share from '@vue/Details/Password/Sharing/Share';
    import SettingsManager from '@js/Manager/SettingsManager';

    export default {
        components: {
            Share,
            Translate
        },

        props: {
            password: {
                type: Object
            }
        },

        data() {
            return {
                search      : '',
                matches     : [],
                nameMap     : [],
                idMap       : [],
                shares      : this.password.shares,
                placeholder : Localisation.translate('Search user'),
                autocomplete: SettingsManager.get('server.sharing.autocomplete'),
                interval    : null,
                polling     : {interval: null, mode: null}
            };
        },

        created() {
            this.startPolling();
        },

        beforeDestroy() {
            this.stopPolling();
        },

        computed: {
            getSharedWithUsers() {
                let users = [];
                for(let i in this.shares) {
                    if(this.shares.hasOwnProperty(i)) users.push(this.shares[i].receiver.id);
                }

                if(this.password.share !== null) {
                    users.push(this.password.share.owner.id);
                }

                return users;
            }
        },

        methods: {
            async searchUsers() {
                if(this.search === '' || !this.autocomplete) {
                    this.matches = [];
                    return;
                }

                let users   = this.getSharedWithUsers,
                    matches = await API.findSharePartners(this.search, users.length + 10);
                this.matches = [];

                for(let i in matches) {
                    if(!matches.hasOwnProperty(i) || users.indexOf(i) !== -1) continue;
                    let name = matches[i];

                    if(this.matches.length < 5) this.matches.push({id: i, name});
                    this.nameMap[name] = i;
                    this.idMap[i] = name;
                }
            },
            addShare(receiver) {
                let share = {
                    password : this.password.id,
                    expires  : null,
                    editable : false,
                    shareable: true,
                    receiver : receiver
                };
                API.createShare(share).then(
                    (d) => {
                        this.getSharedWithUsers.push(receiver);
                        share.id = d.id;
                        share.updatePending = true;
                        share.owner = {
                            id  : document.querySelector('head[data-user]').getAttribute('data-user'),
                            name: document.querySelector('head[data-user-displayname]').getAttribute('data-user-displayname')
                        };
                        share.receiver = {id: receiver, name: this.idMap[receiver]};
                        this.shares[d.id] = API._processShare(share);
                        this.search = '';
                        this.refreshShares();
                    }
                ).catch((e) => {
                    if(e.id === '65782183') {
                        Messages.notification(['The user {uid} does not exist', {uid: receiver}]);
                    } else {
                        let message = e.hasOwnProperty('message') ? e.message:e.statusText;
                        Messages.notification(['Unable to share password: {message}', {message}]);
                    }
                });
            },
            reloadShares() {
                API.showPassword(this.password.id, 'shares')
                    .then((d) => { this.shares = d.shares;});
            },
            submitAction($event) {
                if($event.keyCode === 13) {
                    let uid = this.search;
                    if(this.nameMap.hasOwnProperty(uid)) {
                        uid = this.nameMap[uid];
                    }

                    if(this.idMap.hasOwnProperty(uid) || !this.autocomplete) {
                        this.addShare(uid);
                    } else {
                        Messages.notification(['The user {uid} does not exist', {uid}]);
                    }
                }
            },
            shareWithUser(uid) {
                this.addShare(uid);
            },
            getAvatarUrl(uid) {
                return API.getAvatarUrl(uid);
            },
            deleteShare($event) {
                delete this.shares[$event.id];
                this.refreshShares();
            },
            refreshShares() {
                API.runSharingCron()
                    .then((d) => { if(d.success) this.reloadShares();});

                this.startPolling();
                this.$forceUpdate();
            },
            startPolling(mode = 'fast') {
                if(this.polling.mode === mode) return;
                this.stopPolling();

                let time = mode === 'slow' ? 60000:5000;
                this.polling.interval = setInterval(() => { this.reloadShares(); }, time);
            },
            stopPolling() {
                if(this.polling.interval !== null) {
                    clearInterval(this.polling.interval);
                    this.polling.interval = null;
                    this.polling.mode = null;
                }
            }
        },

        watch: {
            password(value) {
                this.shares = value.shares;
                this.$forceUpdate();
            },
            search() {
                this.searchUsers();
            },
            shares(shares) {
                for(let id in shares) {
                    if(shares.hasOwnProperty(id) && shares[id].updatePending) {
                        API.runSharingCron();
                        this.startPolling();
                        return;
                    }
                }
                this.startPolling('slow');
            }
        }
    };
</script>

<style lang="scss">
    .sharing-container {
        position : relative;

        .share-add-user {
            width : 100%;
        }

        .shares {
            margin-top : 5px;
        }

        .user-search {
            position         : absolute;
            top              : 37px;
            width            : 100%;
            border-radius    : var(--border-radius);
            z-index          : 2;
            background-color : var(--color-main-background);
            color            : var(--color-primary);
            border           : 1px solid var(--color-primary);

            li {
                line-height : 32px;
                display     : flex;
                padding     : 3px;
                cursor      : pointer;

                &:hover {
                    color            : var(--color-primary-text);
                    background-color : var(--color-primary);
                }
            }
        }
    }
</style>