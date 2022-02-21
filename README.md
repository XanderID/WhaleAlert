# General
WhaleAlert in PocketMine!</br>
This plugin is for alerts if there is a large amount of Pay Transactions Economys

# Commands

Commands Toggle for On/Off WhaleAlert
Default if player Joined is On

Commands | Aliases | Default | Permission
--- | --- | --- | ---
`/whalealert` | /whalealert | True | `whalealert.toggle`

# Feature
- Alert if Transaction Minimum Reached
- Multiple Economys
- Can Auto detect Economys Plugin
- Customable Minimum
- Customable Broadcast Message

# To-Do
- Broadcast to Discord

# Config

``` YAML

---
# Your Economy plugin name
# Available: BedrockEconomy, EconomyAPI, Auto
economy: "Auto"

# Message for WhaleAlert
# Tag:
# {name} Sender Name
# {target} Target Name
# {amount} Amount of Transferred Money
# {economy} Name of Economy
# {line} New Line
message: "§a!!!!!! {amount} #{economy}, transfered from Unknow wallet to Unknow wallet"

# Minimum Transferred money for WhaleAlert
minimum: 100000
...
```

# Additional Notes

- If you find bugs or want to give suggestions, please visit [here](https://github.com/MulqiGaming64/WhaleAlert/issues)
- Icons By [icons8.com](https://icons8.com)
