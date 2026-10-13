# ZuriLite anticheat

ZuriLite is a PocketMine-MP API 5 anticheat plugin aimed at combat-oriented practice servers. It loads packet and event checks, applies the actions enabled in `config.yml`, and provides an in-game administration command.

This repository is discontinued as an independently maintained project while compatibility work is concentrated on [Zuri](https://github.com/ReinfyTeam/Zuri). The source remains available for operators who choose to maintain or rewrite it. The check list below describes implemented modules; it is not an independent accuracy or detection-rate guarantee.

## Requirements

- PocketMine-MP API 5.0.0 or a compatible API 5 server.
- A server environment that supports the plugin's PocketMine-MP network, player and event APIs.

ZuriLite does not claim compatibility with unverified PocketMine-MP forks or spoons. Test the exact server build and client behavior used by your network before enabling automatic punishments.

## Included checks

The current source loads these check families and subtypes:

- AutoClick: A, B and C
- Behavior checks: InstaBreak, FastEat, FastThrow, EditionFaker, ProxyBot, AntiBot and Phase
- Fly: A, B and C
- KillAura: A, B and C
- Reach: A and B
- Scaffold: A, B and C
- Speed: A and B
- Velocity: A and B

Individual checks can be enabled or disabled in the `zuri.checks` section. Each check can be configured to flag, kick or ban according to its available options and violation threshold.

## Installation

1. Obtain a PHAR from the configured build/distribution path or build one from this repository.
2. Copy `ZuriLite.phar` into the server's `plugins/` directory.
3. Start the server and review the log for successful plugin enablement.
4. Review `plugin_data/ZuriLite/config.yml` and `plugin_data/ZuriLite/webhook.yml` before enabling punishments or Discord notifications.

The plugin is version `1.0.0` in the current manifest. A stable [`v1.0.0` GitHub release](https://github.com/ReinfyTeam/ZuriLite/releases/tag/v1.0.0) is available, but its artifact targets an earlier source commit; verify the exact artifact and target server separately because no complete client compatibility matrix is claimed.

## Administration command

The `/zuri` command requires the `zuri.command` permission and supports these subcommands:

- `/zuri about` — display the plugin version and loaded-check count.
- `/zuri notify toggle|admin` — toggle alerts or administrator-only alerts.
- `/zuri banmode toggle` — toggle ban mode.
- `/zuri bypass` — toggle bypass mode.
- `/zuri debug` — toggle debug mode for the executing player.
- `/zuri list` — list loaded checks and their configured limits.
- `/zuri ui` — open the player administration form.

The default permission is operator-only. Review permissions and commands before granting access to staff roles.

## Configuration

The plugin copies `resources/config.yml` and `resources/webhook.yml` into its data directory. Important configuration areas include:

- `zuri.world_bypass` for excluding selected worlds.
- `zuri.alerts` for notification text, enablement and permission behavior.
- `zuri.kick` and `zuri.ban` for punishment actions and server commands.
- `zuri.permissions.bypass` for bypass control.
- `zuri.discord` and `webhook.yml` for optional Discord notifications.
- `zuri.debug` for diagnostic logging, which can increase server output and overhead.

Keep the configuration version managed by the plugin. If the configuration is outdated, ZuriLite renames the existing file to `old-config.yml` before writing the packaged default; preserve that file when investigating a migration.

## Development and packaging

The repository's build script packages `src/`, `resources/` and `plugin.yml` into `ZuriLite.phar` and then removes its temporary `build/` directory. A local build requires PHP with the extensions used by the script, including YAML parsing and Phar support:

```text
php -dphar.readonly=0 tools/build.php
```

The GitHub Actions workflows apply PHP code style and build the plugin artifact. The release workflow only proceeds when the commit message contains the existing `Release <version>` marker; it creates a draft release and uploads the generated PHAR. The workflow maintains the exact builder/runtime references, so this README intentionally describes the build by function rather than copying a tool revision.

## License

ZuriLite is licensed under the [GNU Lesser General Public License v2.1](LICENSE).
