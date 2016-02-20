## Semantic UI

To install semantic-ui in this folder, and get it up and running, use:

```
npm install semantic-ui --save
```

Open `semantic/src/definitions/globals/reset.less` and make sure the following is at the bottom of the file:

```
/*************** Custom CSS for Bolt ***************/
& { @import "../../../../custom"; }
```

Please add CSS only to `custom.less`!

Then, go to semantic/ and run `gulp build`, to create the files in `../public/semantic/`
and you're good to go!



