# PerWorldPlayer
Per-world player data (inventories, effects, hp, hunger etc) for PocketMine-MP

This plugin allows servers to separate out player data among worlds (or a group of worlds). It's based on [PerWorldInventory](https://github.com/BlockHorizons/PerWorldInventory) and has a few things in similar.

# Installation
It is mostly recommended to use released versions of the plugin, which can be found in the released version on Poggit. (once there is one)
If you do decide you want the latest version of the plugin, it is recommended you fetch a pre-compiled phar file from Poggit-CI, which can be found below.


# Configurables
1. You can stop players from switching inventories per world by giving them the permission: `per-world-player.bypass`.
2. You can group worlds so the same player data is shared among two (or more) worlds by configuring [`Bundled-Worlds` in `config.yml`](https://github.com/BlockHorizons/PerWorldPlayer/blob/master/resources/config.yml#L43). This can be useful for creative mode servers.
3. You can configure which player data you want to add multi-world support to by modifying [`Save-Data` in `config.yml`](https://github.com/BlockHorizons/PerWorldPlayer/blob/master/resources/config.yml#L5).
