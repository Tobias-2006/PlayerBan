<h1 align="center">PlayerBan</h1>
<p align="center">BanSystem Plugin for Minecraft BE (<a href="https://github.com/pmmp/PocketMine-MP">Pocketmine-MP</a>)</p>
<p align="center">This plugin allows you to ban players from your server who do not follow the rules</p>
<p align="center"><img alt="GitHub top language" src="https://img.shields.io/github/languages/top/Tobias-2006/PlayerBan"> <img alt="GitHub forks" src="https://img.shields.io/github/forks/Tobias-2006/PlayerBan?style=social"> <img alt="GitHub repo size" src="https://img.shields.io/github/repo-size/Tobias-2006/PlayerBan"></p>

## Installation
- Download the latest release
- Put the plugin in the `/plugins` folder of your server
- Now restart the server once

## Why PlayerBan?
PlayerBan is a ban system that is intended to make work easier for team members. Through the punishments, which are determined by the server management, there are uniform punishments for the same rule violations. In addition, you only have to enter an id instead of the reason and duration, which is much quicker to type in. The log of all changes makes it easy to track activities.
<br>

### Future plans
- Discord Webhooks
- More translations

## Commands & Permissions
| Command | Parameters | Description | Permissions |
| :-----: | :-------: | :---------: | :-------: |
| /ban | `<player\|ip>` `<punId>` | Ban a player from the server | `Op`, `playerban.command.ban` |
| /unban | `<player\|ip>` | Unban someone from the server | `Op`, `playerban.command.unban` |
| /banlist | - | Shows a list of all banned players | `Op`, `playerban.command.banlist` |
| /banlogs | - | Shows a modification protocol | `Op`, `playerban.command.banlogs` |
| /punishments | - | Create or edit punishments | `Op`, `playerban.command.punishments` |
| /punlist | - | Displays a list of all punishments | `Op`, `playerban.command.punlist` |
| /banhistory | `<player\|ip>` | Shows a list of all bans of a player | `Op`, `playerban.command.banhistory` |

## License
```
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses.
```
