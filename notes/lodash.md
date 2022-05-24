## Lodash/Underscore Replacements
```typescript
_.filter(list,(param) => {});
list.filter((param) => {});

_.sortBy(list,propertyname);
Peanut.Helper.SortByAlpha(list,propertyname); //  case insensitied
Peanut.Helper.SortByInt(list,propertyname); //  whole number values
Peanut.Helper.SortBy(list,propertyname); //  conversions or case don't matter

_.findindex(list,(item) => {/* return boolean */});
Peanut.Helper.FindIndex(list,(item: any) => {/* return boolean */});

 _.find(list, function(item: any) {/* return boolean */});
 list.find(function(item: any) {/* return boolean */});
 
 
 
```

