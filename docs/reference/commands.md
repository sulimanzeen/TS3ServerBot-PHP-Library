# ServerQuery command matrix

Source: TeamSpeak 3.13.8 Query command help (this table). TeamSpeak 6.0.0-beta12.1 adds nine commands documented in [TeamSpeak 6-only commands](teamspeak6-commands.md). The static docs site command matrix can filter by family.

WebQuery support follows TeamSpeak 3.13.8 and 6.0.0-beta12.1 WebQuery docs (`ft*`, `help`, `login`, `logout`, `quit`, `servernotifyregister`, `servernotifyunregister`, and `use` are SSH-session only).

Framework transports: SSH and WebQuery only. Raw/telnet Query is not implemented.

| Command | Category | WebQuery | SSH | Alias of | Permissions |
|---|---|---|---|---|---|
| `apikeyadd` | query | yes | yes |  | b_virtualserver_apikey_add, b_virtualserver_apikey_manage |
| `apikeydel` | query | yes | yes |  | b_virtualserver_apikey_manage |
| `apikeylist` | query | yes | yes |  | b_virtualserver_apikey_manage |
| `banadd` | query | yes | yes |  | b_client_ban_create |
| `banclient` | query | yes | yes |  | i_client_ban_power, i_client_needed_ban_power |
| `bandel` | query | yes | yes |  | b_client_ban_delete, b_client_ban_delete_own |
| `bandelall` | query | yes | yes |  | b_client_ban_delete |
| `banlist` | query | yes | yes |  | b_client_ban_list |
| `bindinglist` | query | yes | yes |  | b_serverinstance_binding_list |
| `channeladdperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channelclientaddperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channelclientdelperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channelclientpermlist` | query | yes | yes |  | b_virtualserver_channelclient_permission_list |
| `channelcreate` | query | yes | yes |  | i_channel_min_depth, i_channel_max_depth, b_channel_create_child, b_channel_create_permanent, b_channel_create_semi_permanent, b_channel_create_temporary, b_channel_create_with_topic, b_channel_create_with_description, b_channel_create_with_password, b_channel_create_with_banner, i_channel_create_modify_with_codec_maxquality, i_channel_create_modify_with_codec_latency_factor_min, b_channel_create_with_maxclients, b_channel_create_with_maxfamilyclients, b_channel_create_with_sortorder, b_channel_create_with_default, b_channel_create_with_needed_talk_power |
| `channeldelete` | query | yes | yes |  | b_channel_delete_permanent, b_channel_delete_semi_permanent, b_channel_delete_temporary, b_channel_delete_flag_force |
| `channeldelperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channeledit` | query | yes | yes |  | i_channel_min_depth, i_channel_max_depth, b_channel_modify_parent, b_channel_modify_make_default, b_channel_modify_make_permanent, b_channel_modify_make_semi_permanent, b_channel_modify_make_temporary, b_channel_modify_name, b_channel_modify_topic, b_channel_modify_description, b_channel_modify_password, b_channel_modify_banner, b_channel_modify_codec, b_channel_modify_codec_quality, b_channel_create_modify_with_codec_maxquality, b_channel_modify_codec_latency_factor, b_channel_modify_make_codec_encrypted, b_channel_modify_maxclients, b_channel_modify_maxfamilyclients, b_channel_modify_sortorder, b_channel_modify_needed_talk_power, i_channel_modify_power, i_channel_needed_modify_power |
| `channelfind` | query | yes | yes |  | b_virtualserver_channel_search |
| `channelgroupadd` | query | yes | yes |  | b_virtualserver_channelgroup_create |
| `channelgroupaddperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channelgroupclientlist` | query | yes | yes |  | b_virtualserver_channelgroup_client_list |
| `channelgroupcopy` | query | yes | yes |  | b_virtualserver_channelgroup_create, i_group_modify_power, i_group_needed_modify_power |
| `channelgroupdel` | query | yes | yes |  | b_virtualserver_channelgroup_delete |
| `channelgroupdelperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `channelgrouplist` | query | yes | yes |  | b_virtualserver_channelgroup_list, b_serverinstance_modify_templates |
| `channelgrouppermlist` | query | yes | yes |  | b_virtualserver_channelgroup_permission_list |
| `channelgrouprename` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power |
| `channelinfo` | query | yes | yes |  | b_channel_info_view |
| `channellist` | query | yes | yes |  | b_virtualserver_channel_list |
| `channelmove` | query | yes | yes |  | i_channel_min_depth, i_channel_max_depth, b_channel_modify_parent, b_channel_modify_sortorder |
| `channelpermlist` | query | yes | yes |  | b_virtualserver_channel_permission_list |
| `clientaddperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `clientaddservergroup` | query | yes | yes |  | i_group_member_add_power, i_group_needed_member_add_power |
| `clientdbdelete` | query | yes | yes |  | b_client_delete_dbproperties |
| `clientdbedit` | query | yes | yes |  | b_client_modify_dbproperties, b_client_modify_description, b_client_set_talk_power |
| `clientdbfind` | query | yes | yes |  | b_virtualserver_client_dbsearch |
| `clientdbinfo` | query | yes | yes |  | b_virtualserver_client_dbinfo |
| `clientdblist` | query | yes | yes |  | b_virtualserver_client_dblist |
| `clientdelperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `clientdelservergroup` | query | yes | yes |  | i_group_member_remove_power, i_group_needed_member_remove_power |
| `clientedit` | query | yes | yes |  | b_client_modify_description, b_client_set_talk_power |
| `clientfind` | query | yes | yes |  | b_virtualserver_client_search |
| `clientgetdbidfromuid` | query | yes | yes |  |  |
| `clientgetids` | query | yes | yes |  |  |
| `clientgetnamefromdbid` | query | yes | yes |  |  |
| `clientgetnamefromuid` | query | yes | yes |  |  |
| `clientgetuidfromclid` | query | yes | yes |  |  |
| `clientinfo` | query | yes | yes |  | b_client_info_view |
| `clientkick` | query | yes | yes |  | i_client_kick_from_server_power, i_client_kick_from_channel_power, i_client_needed_kick_from_server_power, i_client_needed_kick_from_channel_power |
| `clientlist` | query | yes | yes |  | b_virtualserver_client_list, i_channel_subscribe_power, i_channel_needed_subscribe_power |
| `clientmove` | query | yes | yes |  | i_client_move_power, i_client_needed_move_power |
| `clientpermlist` | query | yes | yes |  | b_virtualserver_client_permission_list |
| `clientpoke` | query | yes | yes |  | i_client_poke_power, i_client_needed_poke_power |
| `clientsetserverquerylogin` | query | yes | yes |  | b_client_create_modify_serverquery_login |
| `clientupdate` | query | yes | yes |  |  |
| `complainadd` | query | yes | yes |  | i_client_complain_power, i_client_needed_complain_power |
| `complaindel` | query | yes | yes |  | b_client_complain_delete, b_client_complain_delete_own |
| `complaindelall` | query | yes | yes |  | b_client_complain_delete |
| `complainlist` | query | yes | yes |  | b_client_complain_list |
| `customdelete` | query | yes | yes |  | b_client_delete_dbproperties |
| `custominfo` | query | yes | yes |  |  |
| `customsearch` | query | yes | yes |  |  |
| `customset` | query | yes | yes |  | b_client_modify_dbproperties |
| `ftcreatedir` | file-transfer-control | no | yes |  | i_ft_directory_create_power, i_ft_needed_file_directory_create_power |
| `ftdeletefile` | file-transfer-control | no | yes |  | i_ft_file_delete_power, i_ft_needed_file_delete_power |
| `ftgetfileinfo` | file-transfer-control | no | yes |  | i_ft_file_browse_power, i_ft_needed_file_browse_power |
| `ftgetfilelist` | file-transfer-control | no | yes |  | i_ft_file_browse_power, i_ft_needed_file_browse_power |
| `ftinitdownload` | file-transfer-control | no | yes |  | i_ft_file_download_power, i_ft_needed_file_, i_ft_quota_mb_ |
| `ftinitupload` | file-transfer-control | no | yes |  | i_ft_file_upload_power, i_ft_needed_file_, i_ft_quota_mb_upload_per_client |
| `ftlist` | file-transfer-control | no | yes |  | b_ft_transfer_list |
| `ftrenamefile` | file-transfer-control | no | yes |  | i_ft_file_rename_power, i_ft_needed_file_rename_power |
| `ftstop` | file-transfer-control | no | yes |  |  |
| `gm` | query | yes | yes |  | b_serverinstance_textmessage_send |
| `hostinfo` | query | yes | yes |  | b_serverinstance_info_view |
| `instanceedit` | query | yes | yes |  | b_serverinstance_modify_settings |
| `instanceinfo` | query | yes | yes |  | b_serverinstance_info_view |
| `logadd` | query | yes | yes |  | b_serverinstance_log_add, b_virtualserver_log_add |
| `login` | session | no | yes |  | b_serverquery_login |
| `logout` | session | no | yes |  | b_serverquery_login |
| `logview` | query | yes | yes |  | b_serverinstance_log_view, b_virtualserver_log_view |
| `messageadd` | query | yes | yes |  |  |
| `messagedel` | query | yes | yes |  |  |
| `messageget` | query | yes | yes |  |  |
| `messagelist` | query | yes | yes |  |  |
| `messageupdateflag` | query | yes | yes |  |  |
| `permfind` | query | yes | yes |  | b_virtualserver_permission_find, b_serverinstance_permission_find |
| `permget` | query | yes | yes |  | b_client_permissionoverview_own |
| `permidgetbyname` | query | yes | yes |  | b_serverinstance_permission_list |
| `permissionlist` | query | yes | yes |  | b_serverinstance_permission_list |
| `permoverview` | query | yes | yes |  | b_client_permissionoverview_view |
| `permreset` | query | yes | yes |  | b_virtualserver_permission_reset |
| `privilegekeyadd` | query | yes | yes |  | b_virtualserver_token_add, i_group_needed_member_add_power, i_group_member_add_power |
| `privilegekeydelete` | query | yes | yes |  | b_virtualserver_token_delete |
| `privilegekeylist` | query | yes | yes |  | b_virtualserver_token_list |
| `privilegekeyuse` | query | yes | yes |  | b_virtualserver_token_use |
| `queryloginadd` | query | yes | yes |  | b_serverquery_login_create |
| `querylogindel` | query | yes | yes |  | b_serverquery_login_delete |
| `queryloginlist` | query | yes | yes |  | b_serverquery_login_list |
| `quit` | session | no | yes |  |  |
| `sendtextmessage` | query | yes | yes |  | i_client_private_textmessage_power, i_client_needed_private_textmessage_power, b_client_server_textmessage_send, b_client_channel_textmessage_send |
| `servercreate` | query | yes | yes |  | b_virtualserver_create |
| `serverdelete` | query | yes | yes |  | b_virtualserver_delete |
| `serveredit` | query | yes | yes |  | b_virtualserver_modify_name, b_virtualserver_modify_welcomemessage, b_virtualserver_modify_maxclients, b_virtualserver_modify_reserved_slots, b_virtualserver_modify_password, b_virtualserver_modify_default_servergroup, b_virtualserver_modify_default_channelgroup, b_virtualserver_modify_default_channeladmingroup, b_virtualserver_modify_ft_settings, b_virtualserver_modify_ft_quotas, b_virtualserver_modify_channel_forced_silence, b_virtualserver_modify_complain, b_virtualserver_modify_antiflood, b_virtualserver_modify_hostmessage, b_virtualserver_modify_hostbanner, b_virtualserver_modify_hostbutton, b_virtualserver_modify_port, b_virtualserver_modify_autostart, b_virtualserver_modify_needed_identity_security_level, b_virtualserver_modify_priority_speaker_dimm_modificator, b_virtualserver_modify_log_settings, b_virtualserver_modify_icon_id, b_virtualserver_modify_weblist, b_virtualserver_modify_min_client_version, b_virtualserver_modify_codec_encryption_mode |
| `servergroupadd` | query | yes | yes |  | b_virtualserver_servergroup_create |
| `servergroupaddclient` | query | yes | yes |  | i_group_member_add_power, i_group_needed_member_add_power |
| `servergroupaddperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `servergroupautoaddperm` | query | yes | yes |  | b_permission_modify_power_ignore |
| `servergroupautodelperm` | query | yes | yes |  | b_permission_modify_power_ignore |
| `servergroupclientlist` | query | yes | yes |  | b_virtualserver_servergroup_client_list |
| `servergroupcopy` | query | yes | yes |  | b_virtualserver_servergroup_create, i_group_modify_power, i_group_needed_modify_power |
| `servergroupdel` | query | yes | yes |  | b_virtualserver_servergroup_delete |
| `servergroupdelclient` | query | yes | yes |  | i_group_member_remove_power, i_group_needed_member_remove_power |
| `servergroupdelperm` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power, i_permission_modify_power |
| `servergrouplist` | query | yes | yes |  | b_serverinstance_modify_querygroup, b_serverinstance_modify_templates, b_virtualserver_servergroup_list |
| `servergrouppermlist` | query | yes | yes |  | b_virtualserver_servergroup_permission_list |
| `servergrouprename` | query | yes | yes |  | i_group_modify_power, i_group_needed_modify_power |
| `servergroupsbyclientid` | query | yes | yes |  |  |
| `serveridgetbyport` | query | yes | yes |  | b_serverinstance_virtualserver_list |
| `serverinfo` | query | yes | yes |  | b_virtualserver_info_view |
| `serverlist` | query | yes | yes |  | b_serverinstance_virtualserver_list |
| `servernotifyregister` | event | no | yes |  | b_virtualserver_notify_register |
| `servernotifyunregister` | event | no | yes |  | b_virtualserver_notify_unregister |
| `serverprocessstop` | query | yes | yes |  | b_serverinstance_stop |
| `serverrequestconnectioninfo` | query | yes | yes |  | b_virtualserver_connectioninfo_view |
| `serversnapshotcreate` | query | yes | yes |  | b_virtualserver_snapshot_create |
| `serversnapshotdeploy` | query | yes | yes |  | b_virtualserver_snapshot_deploy |
| `serverstart` | query | yes | yes |  | b_virtualserver_start_any, b_virtualserver_start |
| `serverstop` | query | yes | yes |  | b_virtualserver_stop_any, b_virtualserver_stop |
| `servertemppasswordadd` | query | yes | yes |  | b_virtualserver_modify_password |
| `servertemppassworddel` | query | yes | yes |  | b_virtualserver_modify_password |
| `servertemppasswordlist` | query | yes | yes |  | b_virtualserver_modify_password |
| `setclientchannelgroup` | query | yes | yes |  | i_group_member_add_power, i_group_needed_member_add_power, i_group_member_remove_power, i_group_needed_member_remove_power |
| `tokenadd` | query | yes | yes | privilegekeyadd |  |
| `tokendelete` | query | yes | yes | privilegekeydelete |  |
| `tokenlist` | query | yes | yes | privilegekeylist |  |
| `tokenuse` | query | yes | yes | privilegekeyuse |  |
| `use` | session | no | yes |  | b_virtualserver_select |
| `version` | query | yes | yes |  |  |
| `whoami` | query | yes | yes |  |  |

## SSH-only commands

- `ftcreatedir`
- `ftdeletefile`
- `ftgetfileinfo`
- `ftgetfilelist`
- `ftinitdownload`
- `ftinitupload`
- `ftlist`
- `ftrenamefile`
- `ftstop`
- `login`
- `logout`
- `quit`
- `servernotifyregister`
- `servernotifyunregister`
- `use`
