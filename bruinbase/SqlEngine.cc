/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include <string>
#include "Bruinbase.h"
#include "SqlEngine.h"


using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

//literally just the same format as select code... right???
RC SqlEngine::selectIndex(int attr, const string& table, const vector<SelCond>& cond, BTreeIndex& bTree){
	
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table
  IndexCursor c;
  rid.pid =0;
  rid.sid=0;
  RC     rc;
  int    key;     
  string value,equalsVal;
  int    count=0;
  int    diff;
  int min=-1, max = -1;
  int equals=-1;
  bool GE = false;
  bool LE = false;
  bool impossible=false;
  bool onlyKey=true;
  //we need to lower the number of reads and move the cursor as far as is possible before starting to read
  if((rc=rf.open(table+".tbl",'r'))<0){
	  return rc;
  }
	//parse the conditions and simplify
	for(int i=0; i < cond.size(); i++){
		
		if(cond[i].attr==1){
			int val = atoi(cond[i].value);
			switch(cond[i].comp){
				case SelCond::EQ:
					if(equals!=-1 && val!=equals)
						impossible=true;
					equals = val;
					break;
				case SelCond::GE:
					if(val> min){
						GE = true;
						min = val;
					}
					break;
				case SelCond::GT:
					if(val > min){
						GE = false;
						min = val;
					}
					break;
				case SelCond::LE:
					if(val < max || max == -1){
						LE = true;
						max = val;
					} 
					break;
				case SelCond::LT:
					if(val < max || max == -1){
						LE = false;
						max = val;
					}
				
			}
		}
		else if (cond[i].attr==2){
			onlyKey=false;
			if(cond[i].comp==SelCond::EQ){
				if(equalsVal=="" || strcmp(value.c_str(),cond[i].value)==0)
					equalsVal = cond[i].value;
				else
					impossible=true;
			}
		}
	}
	if(impossible || max!=-1 && min!=-1 && max < min)
		goto exit_select;
	if( max!=-1 && max==min && !LE && !GE)
		goto exit_select;
  
	if(equals!=-1)
		bTree.locate(equals, c);
	else if(min!=-1 && GE)
		bTree.locate(min,c);
	else if(min!=-1 && !GE)
		bTree.locate(min+1,c);
	else
		bTree.locate(0,c);
	while(bTree.readForward(c,key,rid)==0){
		if(!onlyKey && attr==4){//just getting count so don't check val
			if(min!=-1){
				if(GE && key < min)
					goto exit_select;
				else if(!GE && key<=min)
					goto exit_select;
			}
			if(max!=-1){
				if(LE && key>max)
					goto exit_select;
				else if(!LE && key>=max)
					goto exit_select;
			}
			if(equals!=1 && key!=equals)
				goto exit_select;
			count++;
			continue;
		}
		if((rc=rf.read(rid,key,value) < 0)){
			goto exit_select;
		}
		//conditions on tuple!
		for(int i=0; i < cond.size();i++){
			diff = cond[i].attr==1 ? key-atoi(cond[i].value) : strcmp(value.c_str(),cond[i].value);
			switch(cond[i].comp){ //same code as select lol
				case SelCond::EQ:
					if (diff != 0){ 
						if(cond[i].attr==1)
							goto exit_select;
						goto next_tuple;
					}
					break;
				case SelCond::NE:
					if (diff == 0) goto next_tuple;
					break;
				case SelCond::GT:
					if (diff <= 0) goto next_tuple;
					break;
			    case SelCond::LT:
					if (diff >= 0){
						if(cond[i].attr==1)
							goto exit_select;
						goto next_tuple;
					}
					break;
			    case SelCond::GE:
					if (diff < 0) goto next_tuple;
					break;
			    case SelCond::LE:
					if (diff > 0){
						if(cond[i].attr==1)
							goto exit_select;
						goto next_tuple;
					}
					break;
			}
			count ++;
			switch(attr){
				case 1: //SELECT KEY
					fprintf(stdout,"%d\n",key);
					break;
				case 2: //SELECT VAL
					fprintf(stdout,"%s\n", value.c_str());
					break;
				case 3:
					fprintf(stdout,"%d \"%s\"\n",key,value.c_str());
					break;
			}
			next_tuple:
			fprintf(stdout,"");
		}
	}
	rc = 0;
	exit_select:
	if(attr==4 && rc==0){
		fprintf(stdout, "%d\n",count);
	}
	bTree.close();
	rf.close();
	return rc;
	
}
RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning
  
  RC     rc;
  int    key;     
  string value;
  int    count;
  int    diff;
  BTreeIndex bTree;

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }
  bool conditionChecker = false;
  for(int i=0; i < cond.size(); i++){
	  if((cond[i].attr==1 || cond[i].attr == 2 ) && cond[i].comp!=SelCond::NE)
		conditionChecker=true;
  }
  // scan the table file from the beginning
  if(bTree.open(table+".idx",'r')==0 && (!conditionChecker && attr!=4)){
	 
  
	  rid.pid = rid.sid = 0;
	  count = 0;
	  while (rid < rf.endRid()) {
		// read the tuple
		if ((rc = rf.read(rid, key, value)) < 0) {
		  fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
		  goto exit_select;
		}

		// check the conditions on the tuple
		for (unsigned i = 0; i < cond.size(); i++) {
		  // compute the difference between the tuple value and the condition value
		  switch (cond[i].attr) {
		  case 1:
		diff = key - atoi(cond[i].value);
		break;
		  case 2:
		diff = strcmp(value.c_str(), cond[i].value);
		break;
		  }

		  // skip the tuple if any condition is not met
		  switch (cond[i].comp) {
		  case SelCond::EQ:
		if (diff != 0) goto next_tuple;
		break;
		  case SelCond::NE:
		if (diff == 0) goto next_tuple;
		break;
		  case SelCond::GT:
		if (diff <= 0) goto next_tuple;
		break;
		  case SelCond::LT:
		if (diff >= 0) goto next_tuple;
		break;
		  case SelCond::GE:
		if (diff < 0) goto next_tuple;
		break;
		  case SelCond::LE:
		if (diff > 0) goto next_tuple;
		break;
		  }
		}

		// the condition is met for the tuple. 
		// increase matching tuple counter
		count++;

		// print the tuple 
		switch (attr) {
		case 1:  // SELECT key
		  fprintf(stdout, "%d\n", key);
		  break;
		case 2:  // SELECT value
		  fprintf(stdout, "%s\n", value.c_str());
		  break;
		case 3:  // SELECT *
		  fprintf(stdout, "%d '%s'\n", key, value.c_str());
		  break;
		}

		// move to the next tuple
		next_tuple:
		++rid;
	  }
  }
  else
	   selectIndex(attr, table, cond, bTree);


  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:
  rf.close();
  return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
	RecordFile rf(table+".tbl",'w');
	ifstream in_file;
	in_file.open(loadfile.c_str(),ifstream::in);
	string tuple, value;
	int key;
	RecordId r_id;
	BTreeIndex bTree;
	
	if(! index){
		while(getline(in_file,tuple)){
			if(parseLoadLine(tuple,key,value)!=0){
				fprintf(stderr, "Error: unable to parse line");
				return RC_FILE_WRITE_FAILED;
			}
			if(rf.append(key,value,r_id)!=0){
				fprintf(stderr, "Error: unable to parse line");
				return RC_FILE_WRITE_FAILED;
			}
		}
	}
	else{
		bTree.open(table+".idx",'w');
		while(getline(in_file,tuple)){
			if(parseLoadLine(tuple,key,value)!=0){
				return RC_FILE_WRITE_FAILED;
			}
			if(rf.append(key,value,r_id)!=0){
				return RC_FILE_WRITE_FAILED;
			}
			if(bTree.insert(key,r_id)!=0){
				return RC_FILE_WRITE_FAILED;
			}
		}
		bTree.close();
	}
	in_file.close();
	rf.close();
    return 0;
}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;
    
    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');
    
    // if there is nothing left, set the value to empty string
    if (c == 0) { 
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
